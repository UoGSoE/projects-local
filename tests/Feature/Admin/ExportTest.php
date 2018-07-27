<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Project;
use Tests\TestCase;
use Ohffs\SimpleSpout\ExcelSheet;
use App\Exports\ProjectListExporter;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_download_a_spreadsheet_of_all_project_data()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $project1->students()->sync([$student1->id => ['choice' => 1]]);
        $project2->students()->sync([$student2->id => ['choice' => 2]]);

        $response = $this->actingAs($admin)->get(route('export.projects.excel'));

        $response->assertSuccessful();
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertEquals('attachment; filename=uog_project_data.xlsx', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function an_admin_can_download_a_spreadsheet_of_all_undergrad_project_data()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $project1 = create(Project::class, ['category' => 'undergrad']);
        $project2 = create(Project::class, ['category' => 'postgrad']);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $project1->students()->sync([$student1->id => ['choice' => 1]]);
        $project2->students()->sync([$student2->id => ['choice' => 2]]);

        $response = $this->actingAs($admin)->get(route('export.projects.excel', ['category' => 'undergrad']));

        $response->assertSuccessful();
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertEquals('attachment; filename=uog_undergrad_project_data.xlsx', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function an_admin_can_download_a_spreadsheet_of_all_postgrad_project_data()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $project1 = create(Project::class, ['category' => 'postgrad']);
        $project2 = create(Project::class, ['category' => 'undergrad']);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $project1->students()->sync([$student1->id => ['choice' => 1]]);
        $project2->students()->sync([$student2->id => ['choice' => 2]]);

        $response = $this->actingAs($admin)->get(route('export.projects.excel', ['category' => 'postgrad']));

        $response->assertSuccessful();
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertEquals('attachment; filename=uog_postgrad_project_data.xlsx', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function the_data_in_the_exported_spreadsheet_is_in_the_correct_format()
    {
        $secondSupervisor = create(User::class, ['is_staff' => true]);
        $postgradProject1 = create(Project::class, ['category' => 'postgrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $postgradProject2 = create(Project::class, ['category' => 'postgrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $undergradProject1 = create(Project::class, ['category' => 'undergrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $postgradProject1->students()->sync([$student1->id => ['choice' => 1, 'is_accepted' => true]]);
        $postgradProject2->students()->sync([$student1->id => ['choice' => 2, 'is_accepted' => false]]);
        $undergradProject1->students()->sync([$student2->id => ['choice' => 2, 'is_accepted' => false]]);

        // export the postgrad projects
        $sheet = (new ProjectListExporter(Project::postgrad()->get()))->create();

        // convert the sheet back to an array/collection
        $contents = (new ExcelSheet)->import($sheet);

        // it should have three rows - one header, two postgrad projects, no undergrad projects
        $this->assertCount(3, $contents);

        // row 1 should be postgradProject1 which has one accepted student
        tap($contents[1], function ($project) use ($postgradProject1, $student1) {
            $this->assertCount(15, $project);
            $this->assertEquals($postgradProject1->id, $project[0]);
            $this->assertEquals($postgradProject1->title, $project[1]);
            $this->assertEquals($postgradProject1->owner->username, $project[2]);
            $this->assertEquals($postgradProject1->owner->full_name, $project[3]);
            $this->assertEquals($postgradProject1->secondSupervisor->username, $project[4]);
            $this->assertEquals($postgradProject1->secondSupervisor->full_name, $project[5]);
            $this->assertEquals($postgradProject1->course_codes, $project[6]);
            $this->assertEquals($postgradProject1->category, $project[7]);
            $this->assertEquals($postgradProject1->max_students, $project[8]);
            $this->assertEquals($postgradProject1->is_active ? 'Y' : 'N', $project[9]);
            $this->assertEquals($postgradProject1->is_confidential ? 'Y' : 'N', $project[10]);
            $this->assertEquals($postgradProject1->is_placement ? 'Y' : 'N', $project[11]);
            $this->assertEquals($postgradProject1->description, $project[12]);
            $this->assertEquals($postgradProject1->pre_req, $project[13]);
            $this->assertEquals($student1->full_name, $project[14]);
        });

        // row 2 should be postgradProject2 which has no accepted student
        tap($contents[2], function ($project) use ($postgradProject2) {
            $this->assertCount(14, $project); // ie, one less column than the sheets row[1] above as no accepted student
            $this->assertEquals($postgradProject2->id, $project[0]);
            $this->assertEquals($postgradProject2->title, $project[1]);
            $this->assertEquals($postgradProject2->owner->username, $project[2]);
            $this->assertEquals($postgradProject2->owner->full_name, $project[3]);
            $this->assertEquals($postgradProject2->secondSupervisor->username, $project[4]);
            $this->assertEquals($postgradProject2->secondSupervisor->full_name, $project[5]);
            $this->assertEquals($postgradProject2->course_codes, $project[6]);
            $this->assertEquals($postgradProject2->category, $project[7]);
            $this->assertEquals($postgradProject2->max_students, $project[8]);
            $this->assertEquals($postgradProject2->is_active ? 'Y' : 'N', $project[9]);
            $this->assertEquals($postgradProject2->is_confidential ? 'Y' : 'N', $project[10]);
            $this->assertEquals($postgradProject2->is_placement ? 'Y' : 'N', $project[11]);
            $this->assertEquals($postgradProject2->description, $project[12]);
            $this->assertEquals($postgradProject2->pre_req, $project[13]);
        });
    }
}
