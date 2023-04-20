<?php

namespace Tests\Feature\Admin;

use App\Exports\ProjectListExporter;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ImportSecondSupervisorsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admins_can_import_a_spreadsheet_to_update_project_second_supervisors()
    {
        $this->withoutExceptionHandling();
        // given we have an admin and two members of staff
        $admin = create(User::class, ['is_admin' => true]);
        $sup1 = create(User::class, ['is_staff' => true]);
        $sup2 = create(User::class, ['is_staff' => true]);
        // and we create some projects with those staff as 2nd supervisors
        $project1 = create(Project::class, ['second_supervisor_id' => $sup1->id]);
        $project2 = create(Project::class, ['second_supervisor_id' => $sup1->id]);
        $project3 = create(Project::class, ['second_supervisor_id' => $sup2->id]);
        // and we generate the spreadsheet with those 2nd supervisors in
        $filename = (
            new ProjectListExporter(
                Project::all()
            ))
        ->create();
        // and we then clear out those 2nd supervisors from the projects
        $project1->update(['second_supervisor_id' => null]);
        $project2->update(['second_supervisor_id' => null]);
        $project3->update(['second_supervisor_id' => null]);
        Activity::all()->each->delete();

        // then if we upload the spreadsheet with the supervisors guids in place
        $response = $this->actingAs($admin)->post(route('admin.import.second_supervisors'), [
            'sheet' => new UploadedFile($filename, 'project_list.xlsx', 'application/octet-stream', UPLOAD_ERR_OK, true),
        ]);

        // they should get added back in as 2nd supervisors
        $response->assertStatus(302);
        $this->assertEquals($sup1->id, $project1->fresh()->second_supervisor_id);
        $this->assertEquals($sup1->id, $project2->fresh()->second_supervisor_id);
        $this->assertEquals($sup2->id, $project3->fresh()->second_supervisor_id);
        $log = Activity::first();
        $this->assertTrue($log->causer->is($admin));
        $this->assertEquals('Imported 2nd supervisors', $log->description);
    }

    /** @test */
    public function we_return_errors_for_rows_with_unknown_project_ids_or_guids()
    {
        // given we have an admin and two members of staff
        $admin = create(User::class, ['is_admin' => true]);
        $sup1 = create(User::class, ['is_staff' => true]);
        $sup2 = create(User::class, ['is_staff' => true]);
        // and we create some projects with those staff as 2nd supervisors
        $project1 = create(Project::class, ['second_supervisor_id' => $sup1->id]);
        $project2 = create(Project::class, ['second_supervisor_id' => $sup1->id]);
        $project3 = create(Project::class, ['second_supervisor_id' => $sup2->id]);
        // and we generate the spreadsheet with those 2nd supervisors in
        $filename = (new ProjectListExporter(Project::all()))->create();
        // and we then delete some of the records so they will no longer be found
        $project2->delete();
        $sup2->delete();

        // then if we upload the spreadsheet with the deleted supervisors guid & project id in place
        $response = $this->actingAs($admin)->post(route('admin.import.second_supervisors'), [
            'sheet' => new UploadedFile($filename, 'project_list.xlsx', 'application/octet-stream', UPLOAD_ERR_OK, true),
        ]);

        // we should have errors about the missing project and 2nd supervisor
        $response->assertStatus(302);
        $response->assertSessionHasErrors("projectnotfound-{$project2->id}");
        $response->assertSessionHasErrors("usernotfound-{$sup2->username}");
    }
}
