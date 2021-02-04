<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature\Admin\Export;

use App\Exports\ProjectListExporter;
use App\Exports\ProjectsExport;
use App\Models\Programme;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Maatwebsite\Excel\Facades\Excel;
use Ohffs\SimpleSpout\ExcelSheet;
use Tests\TestCase;

class ProjectExportTest extends TestCase
{
    /** @test */
    public function an_admin_can_download_a_csv_of_all_undergrad_project_data()
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $secondSupervisor = create(User::class, ['is_staff' => true]);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);
        $project1 = create(Project::class, ['title' => 'Aaa', 'category' => 'undergrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $project2 = create(Project::class, ['title' => 'Bbb', 'category' => 'undergrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $postgradProject = create(Project::class, ['title' => 'Ccc', 'category' => 'postgrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $project1->programmes()->sync([$programme1->id, $programme2->id]);
        $project2->programmes()->sync([$programme2->id]);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $project1->students()->sync([$student1->id => ['choice' => 1, 'is_accepted' => true]]);
        $project2->students()->sync([$student1->id => ['choice' => 2, 'is_accepted' => false]]);

        $response = $this->actingAs($admin)->get(route('export.projects', ['category' => 'undergrad', 'format' => 'csv']));

        $response->assertOk();
        Excel::assertDownloaded('uog_undergrad_project_data.csv', function (ProjectsExport $export) use ($student1, $student2, $project1, $project2, $programme1, $programme2) {
            //3 rows, 2 projects + headers
            $this->assertCount(3, $export->collection());

            $this->assertEquals($project1->id, $export->collection()[1]['id']);
            $this->assertEquals($project1->title, $export->collection()[1]['title']);
            $this->assertEquals($project1->owner->username, $export->collection()[1]['owner_guid']);
            $this->assertEquals($project1->owner->full_name, $export->collection()[1]['owner_name']);
            $this->assertEquals($project1->secondSupervisor->username, $export->collection()[1]['2nd_supervisor_guid']);
            $this->assertEquals($project1->secondSupervisor->full_name, $export->collection()[1]['2nd_supervisor_name']);
            $this->assertEquals($project1->course_codes, $export->collection()[1]['course_codes']);
            $this->assertEquals($project1->category, $export->collection()[1]['category']);
            $this->assertEquals($project1->max_students, $export->collection()[1]['max_students']);
            $this->assertEquals($project1->is_active ? 'Y' : 'N', $export->collection()[1]['is_active']);
            $this->assertEquals($project1->is_confidential ? 'Y' : 'N', $export->collection()[1]['is_confidential']);
            $this->assertEquals($project1->is_placement ? 'Y' : 'N', $export->collection()[1]['is_placement']);
            $this->assertEquals($project1->description, $export->collection()[1]['description']);
            $this->assertEquals($project1->pre_req, $export->collection()[1]['pre_req']);
            $this->assertEquals($programme1->title . '|' . $programme2->title, $export->collection()[1]['programmes']);
            $this->assertEquals($student1->full_name, $export->collection()[1]['student_1']);
            $this->assertEquals($project2->id, $export->collection()[2]['id']);
            $this->assertEquals($project2->title, $export->collection()[2]['title']);
            $this->assertEquals($project2->owner->username, $export->collection()[2]['owner_guid']);
            $this->assertEquals($project2->owner->full_name, $export->collection()[2]['owner_name']);
            $this->assertEquals($project2->secondSupervisor->username, $export->collection()[2]['2nd_supervisor_guid']);
            $this->assertEquals($project2->secondSupervisor->full_name, $export->collection()[2]['2nd_supervisor_name']);
            $this->assertEquals($project2->course_codes, $export->collection()[2]['course_codes']);
            $this->assertEquals($project2->category, $export->collection()[2]['category']);
            $this->assertEquals($project2->max_students, $export->collection()[2]['max_students']);
            $this->assertEquals($project2->is_active ? 'Y' : 'N', $export->collection()[2]['is_active']);
            $this->assertEquals($project2->is_confidential ? 'Y' : 'N', $export->collection()[2]['is_confidential']);
            $this->assertEquals($project2->is_placement ? 'Y' : 'N', $export->collection()[2]['is_placement']);
            $this->assertEquals($project2->description, $export->collection()[2]['description']);
            $this->assertEquals($project2->pre_req, $export->collection()[2]['pre_req']);
            $this->assertEquals($programme2->title, $export->collection()[2]['programmes']);

            return true;
        });
    }

    /** @test */
    public function an_admin_can_download_a_csv_of_all_postgrad_project_data()
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $secondSupervisor = create(User::class, ['is_staff' => true]);
        $project1 = create(Project::class, ['title' => 'Aaa', 'category' => 'postgrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $project2 = create(Project::class, ['title' => 'Bbb', 'category' => 'postgrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $undergradProject = create(Project::class, ['title' => 'Bbb', 'category' => 'undergrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $project1->students()->sync([$student1->id => ['choice' => 1, 'is_accepted' => true]]);
        $project2->students()->sync([$student1->id => ['choice' => 2, 'is_accepted' => false]]);

        $response = $this->actingAs($admin)->get(route('export.projects', ['category' => 'postgrad', 'format' => 'csv']));

        $response->assertOk();
        Excel::assertDownloaded('uog_postgrad_project_data.csv', function (ProjectsExport $export) use ($student1, $student2, $project1, $project2) {
            //3 rows, 2 projects + headers
            $this->assertCount(3, $export->collection());

            $this->assertEquals($project1->id, $export->collection()[1]['id']);
            $this->assertEquals($project1->title, $export->collection()[1]['title']);
            $this->assertEquals($project1->owner->username, $export->collection()[1]['owner_guid']);
            $this->assertEquals($project1->owner->full_name, $export->collection()[1]['owner_name']);
            $this->assertEquals($project1->secondSupervisor->username, $export->collection()[1]['2nd_supervisor_guid']);
            $this->assertEquals($project1->secondSupervisor->full_name, $export->collection()[1]['2nd_supervisor_name']);
            $this->assertEquals($project1->course_codes, $export->collection()[1]['course_codes']);
            $this->assertEquals($project1->category, $export->collection()[1]['category']);
            $this->assertEquals($project1->max_students, $export->collection()[1]['max_students']);
            $this->assertEquals($project1->is_active ? 'Y' : 'N', $export->collection()[1]['is_active']);
            $this->assertEquals($project1->is_confidential ? 'Y' : 'N', $export->collection()[1]['is_confidential']);
            $this->assertEquals($project1->is_placement ? 'Y' : 'N', $export->collection()[1]['is_placement']);
            $this->assertEquals($project1->description, $export->collection()[1]['description']);
            $this->assertEquals($project1->pre_req, $export->collection()[1]['pre_req']);
            $this->assertEquals($student1->full_name, $export->collection()[1]['student_1']);

            $this->assertEquals($project2->id, $export->collection()[2]['id']);
            $this->assertEquals($project2->title, $export->collection()[2]['title']);
            $this->assertEquals($project2->owner->username, $export->collection()[2]['owner_guid']);
            $this->assertEquals($project2->owner->full_name, $export->collection()[2]['owner_name']);
            $this->assertEquals($project2->secondSupervisor->username, $export->collection()[2]['2nd_supervisor_guid']);
            $this->assertEquals($project2->secondSupervisor->full_name, $export->collection()[2]['2nd_supervisor_name']);
            $this->assertEquals($project2->course_codes, $export->collection()[2]['course_codes']);
            $this->assertEquals($project2->category, $export->collection()[2]['category']);
            $this->assertEquals($project2->max_students, $export->collection()[2]['max_students']);
            $this->assertEquals($project2->is_active ? 'Y' : 'N', $export->collection()[2]['is_active']);
            $this->assertEquals($project2->is_confidential ? 'Y' : 'N', $export->collection()[2]['is_confidential']);
            $this->assertEquals($project2->is_placement ? 'Y' : 'N', $export->collection()[2]['is_placement']);
            $this->assertEquals($project2->description, $export->collection()[2]['description']);
            $this->assertEquals($project2->pre_req, $export->collection()[2]['pre_req']);

            return true;
        });
    }

    /** @test */
    public function an_admin_can_download_an_excel_of_all_undergrad_project_data()
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $secondSupervisor = create(User::class, ['is_staff' => true]);
        $project1 = create(Project::class, ['title' => 'Aaa', 'category' => 'undergrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $project2 = create(Project::class, ['title' => 'Bbb', 'category' => 'undergrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $postgradProject = create(Project::class, ['title' => 'Ccc', 'category' => 'postgrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $project1->students()->sync([$student1->id => ['choice' => 1, 'is_accepted' => true]]);
        $project2->students()->sync([$student1->id => ['choice' => 2, 'is_accepted' => false]]);

        $response = $this->actingAs($admin)->get(route('export.projects', ['category' => 'undergrad', 'format' => 'xlsx']));

        $response->assertOk();
        Excel::assertDownloaded('uog_undergrad_project_data.xlsx', function (ProjectsExport $export) use ($student1, $student2, $project1, $project2) {
            //3 rows, 2 projects + headers
            $this->assertCount(3, $export->collection());

            $this->assertEquals($project1->id, $export->collection()[1]['id']);
            $this->assertEquals($project1->title, $export->collection()[1]['title']);
            $this->assertEquals($project1->owner->username, $export->collection()[1]['owner_guid']);
            $this->assertEquals($project1->owner->full_name, $export->collection()[1]['owner_name']);
            $this->assertEquals($project1->secondSupervisor->username, $export->collection()[1]['2nd_supervisor_guid']);
            $this->assertEquals($project1->secondSupervisor->full_name, $export->collection()[1]['2nd_supervisor_name']);
            $this->assertEquals($project1->course_codes, $export->collection()[1]['course_codes']);
            $this->assertEquals($project1->category, $export->collection()[1]['category']);
            $this->assertEquals($project1->max_students, $export->collection()[1]['max_students']);
            $this->assertEquals($project1->is_active ? 'Y' : 'N', $export->collection()[1]['is_active']);
            $this->assertEquals($project1->is_confidential ? 'Y' : 'N', $export->collection()[1]['is_confidential']);
            $this->assertEquals($project1->is_placement ? 'Y' : 'N', $export->collection()[1]['is_placement']);
            $this->assertEquals($project1->description, $export->collection()[1]['description']);
            $this->assertEquals($project1->pre_req, $export->collection()[1]['pre_req']);
            $this->assertEquals($student1->full_name, $export->collection()[1]['student_1']);

            $this->assertEquals($project2->id, $export->collection()[2]['id']);
            $this->assertEquals($project2->title, $export->collection()[2]['title']);
            $this->assertEquals($project2->owner->username, $export->collection()[2]['owner_guid']);
            $this->assertEquals($project2->owner->full_name, $export->collection()[2]['owner_name']);
            $this->assertEquals($project2->secondSupervisor->username, $export->collection()[2]['2nd_supervisor_guid']);
            $this->assertEquals($project2->secondSupervisor->full_name, $export->collection()[2]['2nd_supervisor_name']);
            $this->assertEquals($project2->course_codes, $export->collection()[2]['course_codes']);
            $this->assertEquals($project2->category, $export->collection()[2]['category']);
            $this->assertEquals($project2->max_students, $export->collection()[2]['max_students']);
            $this->assertEquals($project2->is_active ? 'Y' : 'N', $export->collection()[2]['is_active']);
            $this->assertEquals($project2->is_confidential ? 'Y' : 'N', $export->collection()[2]['is_confidential']);
            $this->assertEquals($project2->is_placement ? 'Y' : 'N', $export->collection()[2]['is_placement']);
            $this->assertEquals($project2->description, $export->collection()[2]['description']);
            $this->assertEquals($project2->pre_req, $export->collection()[2]['pre_req']);

            return true;
        });
    }

    /** @test */
    public function an_admin_can_download_an_excel_of_all_postgrad_project_data()
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $secondSupervisor = create(User::class, ['is_staff' => true]);
        $project1 = create(Project::class, ['title' => 'Aaa', 'category' => 'postgrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $project2 = create(Project::class, ['title' => 'Bbb', 'category' => 'postgrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $undergradProject = create(Project::class, ['title' => 'Bbb', 'category' => 'undergrad', 'second_supervisor_id' => $secondSupervisor->id]);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $project1->students()->sync([$student1->id => ['choice' => 1, 'is_accepted' => true]]);
        $project2->students()->sync([$student1->id => ['choice' => 2, 'is_accepted' => false]]);

        $response = $this->actingAs($admin)->get(route('export.projects', ['category' => 'postgrad', 'format' => 'xlsx']));

        $response->assertOk();
        Excel::assertDownloaded('uog_postgrad_project_data.xlsx', function (ProjectsExport $export) use ($student1, $student2, $project1, $project2) {
            //3 rows, 2 projects + headers
            $this->assertCount(3, $export->collection());

            $this->assertEquals($project1->id, $export->collection()[1]['id']);
            $this->assertEquals($project1->title, $export->collection()[1]['title']);
            $this->assertEquals($project1->owner->username, $export->collection()[1]['owner_guid']);
            $this->assertEquals($project1->owner->full_name, $export->collection()[1]['owner_name']);
            $this->assertEquals($project1->secondSupervisor->username, $export->collection()[1]['2nd_supervisor_guid']);
            $this->assertEquals($project1->secondSupervisor->full_name, $export->collection()[1]['2nd_supervisor_name']);
            $this->assertEquals($project1->course_codes, $export->collection()[1]['course_codes']);
            $this->assertEquals($project1->category, $export->collection()[1]['category']);
            $this->assertEquals($project1->max_students, $export->collection()[1]['max_students']);
            $this->assertEquals($project1->is_active ? 'Y' : 'N', $export->collection()[1]['is_active']);
            $this->assertEquals($project1->is_confidential ? 'Y' : 'N', $export->collection()[1]['is_confidential']);
            $this->assertEquals($project1->is_placement ? 'Y' : 'N', $export->collection()[1]['is_placement']);
            $this->assertEquals($project1->description, $export->collection()[1]['description']);
            $this->assertEquals($project1->pre_req, $export->collection()[1]['pre_req']);
            $this->assertEquals($student1->full_name, $export->collection()[1]['student_1']);

            $this->assertEquals($project2->id, $export->collection()[2]['id']);
            $this->assertEquals($project2->title, $export->collection()[2]['title']);
            $this->assertEquals($project2->owner->username, $export->collection()[2]['owner_guid']);
            $this->assertEquals($project2->owner->full_name, $export->collection()[2]['owner_name']);
            $this->assertEquals($project2->secondSupervisor->username, $export->collection()[2]['2nd_supervisor_guid']);
            $this->assertEquals($project2->secondSupervisor->full_name, $export->collection()[2]['2nd_supervisor_name']);
            $this->assertEquals($project2->course_codes, $export->collection()[2]['course_codes']);
            $this->assertEquals($project2->category, $export->collection()[2]['category']);
            $this->assertEquals($project2->max_students, $export->collection()[2]['max_students']);
            $this->assertEquals($project2->is_active ? 'Y' : 'N', $export->collection()[2]['is_active']);
            $this->assertEquals($project2->is_confidential ? 'Y' : 'N', $export->collection()[2]['is_confidential']);
            $this->assertEquals($project2->is_placement ? 'Y' : 'N', $export->collection()[2]['is_placement']);
            $this->assertEquals($project2->description, $export->collection()[2]['description']);
            $this->assertEquals($project2->pre_req, $export->collection()[2]['pre_req']);

            return true;
        });
    }
}
