<?php

namespace Tests\Feature\Admin;

use App\Jobs\ImportDmoranSheetRow;
use App\Mail\DMoranSpreadsheetImportCompleteMail;
use App\Models\Course;
use App\Models\Programme;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class ImportMoranSpreadsheetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admins_can_see_the_import_page()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);

        $response = $this->actingAs($admin)->get(route('import.show_moran_importer'));

        $response->assertOk();
        $response->assertSee('Import custom project spreadsheet');
    }

    /** @test */
    public function when_the_sheet_is_uploaded_a_job_is_queued_to_parse_the_information()
    {
        $this->withoutExceptionHandling();
        Queue::fake();
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);

        $filename = './tests/Feature/data/dmoran.xlsx';

        $response = $this->actingAs($admin)->post(route('import.moran_importer'), [
            'sheet' => new UploadedFile($filename, 'dmoran.xlsx', 'application/octet-stream', UPLOAD_ERR_OK, true),
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
        Queue::assertPushed(ImportDmoranSheetRow::class, 12); // number of rows in the fixture spreadsheet
    }

    /** @test */
    public function when_the_sheet_import_finishes_an_email_is_sent_to_the_person_who_requested_it()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);

        $filename = './tests/Feature/data/dmoran.xlsx';

        $response = $this->actingAs($admin)->post(route('import.moran_importer'), [
            'sheet' => new UploadedFile($filename, 'dmoran.xlsx', 'application/octet-stream', UPLOAD_ERR_OK, true),
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
        Mail::assertQueued(DMoranSpreadsheetImportCompleteMail::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin->email);
        });
    }

    /** @test */
    public function when_the_sheet_is_being_imported_all_existing_meng_projects_are_deleted()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
        $meng1 = Project::factory()->create(['type' => 'M.Eng']);
        $meng2 = Project::factory()->create(['type' => 'M.Eng']);
        $inactiveMeng = Project::factory()->create(['type' => 'M.Eng', 'is_active' => false]);
        $beng1 = Project::factory()->create(['type' => 'B.Eng']);
        $msc1 = Project::factory()->create(['type' => '']);

        $filename = './tests/Feature/data/dmoran.xlsx';

        $response = $this->actingAs($admin)->post(route('import.moran_importer'), [
            'sheet' => new UploadedFile($filename, 'dmoran.xlsx', 'application/octet-stream', UPLOAD_ERR_OK, true),
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseMissing('projects', ['id' => $meng1->id]);
        $this->assertDatabaseMissing('projects', ['id' => $meng2->id]);
        $this->assertDatabaseHas('projects', ['id' => $inactiveMeng->id]);
        $this->assertDatabaseHas('projects', ['id' => $beng1->id]);
        $this->assertDatabaseHas('projects', ['id' => $msc1->id]);
    }

    /** @test */
    public function the_import_job_processes_valid_rows()
    {
        $fakeStaff = User::factory()->create(['username' => 'ab123c']);
        $fakeCourse = Course::factory()->create(['code' => 'ENG1234']);
        $fakeProgramme1 = Programme::factory()->create(['title' => 'Aeronautical Engineering [MEng]']);
        $fakeProgramme2 = Programme::factory()->create(['title' => 'Aerospace Systems [MEng]']);

        $row = [
            'An amazing project',
            'ab123c',
            'Smith, Jim',
            'Aero',
            'ENG1234',
            '1',
            'Y',
            'N',
            'N',
            'This project has a long description',
            'Pre reqs go here',
            'Aeronautical Engineering [MEng]|Aerospace Systems [MEng]',
        ];

        ImportDmoranSheetRow::dispatchSync($row, 1);

        tap(Project::first(), function ($project) use ($fakeStaff, $fakeCourse, $fakeProgramme1, $fakeProgramme2) {
            $this->assertEquals('An amazing project', $project->title);
            $this->assertTrue($project->owner->is($fakeStaff));
            $this->assertTrue($project->courses()->first()->is($fakeCourse));
            $this->assertEquals('This project has a long description', $project->description);
            $this->assertEquals('Pre reqs go here', $project->pre_req);
            $this->assertEquals(1, $project->max_students);
            $this->assertTrue($project->isActive());
            $this->assertFalse($project->isConfidential());
            $this->assertFalse($project->isPlacement());
            $this->assertCount(2, $project->programmes);
            $this->assertTrue($project->programmes->contains($fakeProgramme1));
            $this->assertTrue($project->programmes->contains($fakeProgramme2));
        });
    }

    /** @test */
    public function invalid_rows_are_stored_in_redis()
    {
        $fakeStaff = User::factory()->create(['username' => 'ab123c']);
        $fakeCourse = Course::factory()->create(['code' => 'ENG1234']);
        $fakeProgramme1 = Programme::factory()->create(['title' => 'Aeronautical Engineering [MEng]']);
        $fakeProgramme2 = Programme::factory()->create(['title' => 'Aerospace Systems [MEng]']);

        Redis::shouldReceive('sadd')->times(2)->andReturn(true);

        $row = [
            'An amazing project',
            'ab123c',
            'Smith, Jim',
            'Aero',
            'ENG1234',
            '1',
            'Y',
            'N',
            'N',
            'This project has a long description',
            'Pre reqs go here',
            'Not a real programme|Also not a real programme',
        ];

        ImportDmoranSheetRow::dispatchSync($row, 1);
    }

    /** @test */
    public function invalid_rows_are_shown_in_the_email_that_is_sent_when_the_import_is_complete()
    {
        $fakeStaff = User::factory()->create(['username' => 'ab123c']);

        Redis::shouldReceive('smembers')->with('test-errors')->times(1)->andReturn([
            'Invalid GUID abc123x on row 1',
            'Invalid programme Blah de Blah on row 20',
        ]);
        Redis::shouldReceive('del')->once();

        $mail = new DMoranSpreadsheetImportCompleteMail('test-errors');

        $mail->assertSeeInHtml('Invalid GUID abc123x on row 1');
        $mail->assertSeeInHtml('Invalid programme Blah de Blah on row 20');
    }
}
