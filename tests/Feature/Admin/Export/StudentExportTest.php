<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature\Admin\Export;

use App\Exports\StudentsExport;
use App\Models\Course;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class StudentExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_export_the_list_of_undergrads_as_a_csv(): void
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $student1 = User::factory()->student()->create(['is_staff' => false, 'surname' => 'Aaa']);
        $student2 = User::factory()->student()->create(['is_staff' => false, 'surname' => 'Bbb']);
        $course->students()->saveMany([$student1, $student2]);
        $project1 = create(Project::class, ['category' => 'undergrad']);
        $project2 = create(Project::class, ['category' => 'undergrad']);
        $project3 = create(Project::class, ['category' => 'undergrad']);
        $project4 = create(Project::class, ['category' => 'undergrad']);
        $project5 = create(Project::class, ['category' => 'undergrad']);
        $project1->addAndAccept($student1);
        $student2->projects()->sync([
            $project1->id => ['choice' => 1],
            $project2->id => ['choice' => 2],
            $project3->id => ['choice' => 3],
            $project4->id => ['choice' => 4],
            $project5->id => ['choice' => 5],
        ]);

        $response = $this->actingAs($admin)->get(route('export.undergrad', 'csv'));

        $response->assertOk();
        Excel::assertDownloaded('uog_undergrad_project_students.csv', function (StudentsExport $export) use ($student1, $student2, $project1, $project2, $project3, $project4, $project5) {
            //3 rows, 2 students + headers
            $this->assertCount(3, $export->collection());

            $this->assertEquals($student1->matric, $export->collection()[1]['matric']);
            $this->assertEquals($student1->surname, $export->collection()[1]['surname']);
            $this->assertEquals($student1->forenames, $export->collection()[1]['forenames']);
            $this->assertEquals($student1->programme->plan_code, $export->collection()[1]['plan_code']);
            $this->assertEquals($project1->id, $export->collection()[1]['choice_1']);
            $this->assertEquals($project1->title, $export->collection()[1]['choice_1_title']);
            $this->assertEquals('', $export->collection()[1]['choice_2']);
            $this->assertEquals('', $export->collection()[1]['choice_2_title']);
            $this->assertEquals('', $export->collection()[1]['choice_3']);
            $this->assertEquals('', $export->collection()[1]['choice_3_title']);
            $this->assertEquals('', $export->collection()[1]['choice_4']);
            $this->assertEquals('', $export->collection()[1]['choice_4_title']);
            $this->assertEquals('', $export->collection()[1]['choice_5']);
            $this->assertEquals('', $export->collection()[1]['choice_5_title']);

            $this->assertEquals($student2->matric, $export->collection()[2]['matric']);
            $this->assertEquals($student2->surname, $export->collection()[2]['surname']);
            $this->assertEquals($student2->forenames, $export->collection()[2]['forenames']);
            $this->assertEquals($student2->programme->plan_code, $export->collection()[2]['plan_code']);
            $this->assertEquals($project1->id, $export->collection()[2]['choice_1']);
            $this->assertEquals($project1->title, $export->collection()[2]['choice_1_title']);
            $this->assertEquals($project2->id, $export->collection()[2]['choice_2']);
            $this->assertEquals($project2->title, $export->collection()[2]['choice_2_title']);
            $this->assertEquals($project3->id, $export->collection()[2]['choice_3']);
            $this->assertEquals($project3->title, $export->collection()[2]['choice_3_title']);
            $this->assertEquals($project4->id, $export->collection()[2]['choice_4']);
            $this->assertEquals($project4->title, $export->collection()[2]['choice_4_title']);
            $this->assertEquals($project5->id, $export->collection()[2]['choice_5']);
            $this->assertEquals($project5->title, $export->collection()[2]['choice_5_title']);

            return true;
        });
    }

    /** @test */
    public function exporting_students_who_dont_have_a_programme_works_ok(): void
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $student1 = User::factory()->student()->create(['is_staff' => false, 'surname' => 'Aaa']);
        $student2 = User::factory()->student()->create(['programme_id' => null, 'is_staff' => false, 'surname' => 'Bbb']);
        $course->students()->saveMany([$student1, $student2]);
        $project1 = create(Project::class, ['category' => 'undergrad']);
        $project2 = create(Project::class, ['category' => 'undergrad']);
        $project3 = create(Project::class, ['category' => 'undergrad']);
        $project4 = create(Project::class, ['category' => 'undergrad']);
        $project5 = create(Project::class, ['category' => 'undergrad']);
        $project1->addAndAccept($student1);
        $student2->projects()->sync([
            $project1->id => ['choice' => 1],
            $project2->id => ['choice' => 2],
            $project3->id => ['choice' => 3],
            $project4->id => ['choice' => 4],
            $project5->id => ['choice' => 5],
        ]);

        $response = $this->actingAs($admin)->get(route('export.undergrad', 'csv'));

        $response->assertOk();
        Excel::assertDownloaded('uog_undergrad_project_students.csv', function (StudentsExport $export) use ($student1, $student2, $project1, $project2, $project3, $project4, $project5) {
            //3 rows, 2 students + headers
            $this->assertCount(3, $export->collection());

            $this->assertEquals($student1->matric, $export->collection()[1]['matric']);
            $this->assertEquals($student1->surname, $export->collection()[1]['surname']);
            $this->assertEquals($student1->forenames, $export->collection()[1]['forenames']);
            $this->assertEquals($student1->programme->plan_code, $export->collection()[1]['plan_code']);
            $this->assertEquals($project1->id, $export->collection()[1]['choice_1']);
            $this->assertEquals($project1->title, $export->collection()[1]['choice_1_title']);
            $this->assertEquals('', $export->collection()[1]['choice_2']);
            $this->assertEquals('', $export->collection()[1]['choice_2_title']);
            $this->assertEquals('', $export->collection()[1]['choice_3']);
            $this->assertEquals('', $export->collection()[1]['choice_3_title']);
            $this->assertEquals('', $export->collection()[1]['choice_4']);
            $this->assertEquals('', $export->collection()[1]['choice_4_title']);
            $this->assertEquals('', $export->collection()[1]['choice_5']);
            $this->assertEquals('', $export->collection()[1]['choice_5_title']);

            $this->assertEquals($student2->matric, $export->collection()[2]['matric']);
            $this->assertEquals($student2->surname, $export->collection()[2]['surname']);
            $this->assertEquals($student2->forenames, $export->collection()[2]['forenames']);
            $this->assertEquals('', $export->collection()[2]['plan_code']);
            $this->assertEquals($project1->id, $export->collection()[2]['choice_1']);
            $this->assertEquals($project1->title, $export->collection()[2]['choice_1_title']);
            $this->assertEquals($project2->id, $export->collection()[2]['choice_2']);
            $this->assertEquals($project2->title, $export->collection()[2]['choice_2_title']);
            $this->assertEquals($project3->id, $export->collection()[2]['choice_3']);
            $this->assertEquals($project3->title, $export->collection()[2]['choice_3_title']);
            $this->assertEquals($project4->id, $export->collection()[2]['choice_4']);
            $this->assertEquals($project4->title, $export->collection()[2]['choice_4_title']);
            $this->assertEquals($project5->id, $export->collection()[2]['choice_5']);
            $this->assertEquals($project5->title, $export->collection()[2]['choice_5_title']);

            return true;
        });
    }

    /** @test */
    public function an_admin_can_export_the_list_of_undergrads_as_an_xlsx(): void
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $student1 = User::factory()->student()->create(['is_staff' => false, 'surname' => 'Aaa']);
        $student2 = User::factory()->student()->create(['is_staff' => false, 'surname' => 'Bbb']);
        $course->students()->saveMany([$student1, $student2]);
        $project1 = create(Project::class, ['category' => 'undergrad']);
        $project2 = create(Project::class, ['category' => 'undergrad']);
        $project3 = create(Project::class, ['category' => 'undergrad']);
        $project4 = create(Project::class, ['category' => 'undergrad']);
        $project5 = create(Project::class, ['category' => 'undergrad']);
        $project1->addAndAccept($student1);
        $student2->projects()->sync([
            $project1->id => ['choice' => 1],
            $project2->id => ['choice' => 2],
            $project3->id => ['choice' => 3],
            $project4->id => ['choice' => 4],
            $project5->id => ['choice' => 5],
        ]);

        $response = $this->actingAs($admin)->get(route('export.undergrad', 'xlsx'));

        $response->assertOk();
        Excel::assertDownloaded('uog_undergrad_project_students.xlsx', function (StudentsExport $export) use ($student1, $student2, $project1, $project2, $project3, $project4, $project5) {
            //3 rows, 2 students + headers
            $this->assertCount(3, $export->collection());

            $this->assertEquals($student1->matric, $export->collection()[1]['matric']);
            $this->assertEquals($student1->surname, $export->collection()[1]['surname']);
            $this->assertEquals($student1->forenames, $export->collection()[1]['forenames']);
            $this->assertEquals($student1->programme->plan_code, $export->collection()[1]['plan_code']);
            $this->assertEquals($project1->id, $export->collection()[1]['choice_1']);
            $this->assertEquals($project1->title, $export->collection()[1]['choice_1_title']);
            $this->assertEquals('', $export->collection()[1]['choice_2']);
            $this->assertEquals('', $export->collection()[1]['choice_2_title']);
            $this->assertEquals('', $export->collection()[1]['choice_3']);
            $this->assertEquals('', $export->collection()[1]['choice_3_title']);
            $this->assertEquals('', $export->collection()[1]['choice_4']);
            $this->assertEquals('', $export->collection()[1]['choice_4_title']);
            $this->assertEquals('', $export->collection()[1]['choice_5']);
            $this->assertEquals('', $export->collection()[1]['choice_5_title']);

            $this->assertEquals($student2->matric, $export->collection()[2]['matric']);
            $this->assertEquals($student2->surname, $export->collection()[2]['surname']);
            $this->assertEquals($student2->forenames, $export->collection()[2]['forenames']);
            $this->assertEquals($student2->programme->plan_code, $export->collection()[2]['plan_code']);
            $this->assertEquals($project1->id, $export->collection()[2]['choice_1']);
            $this->assertEquals($project1->title, $export->collection()[2]['choice_1_title']);
            $this->assertEquals($project2->id, $export->collection()[2]['choice_2']);
            $this->assertEquals($project2->title, $export->collection()[2]['choice_2_title']);
            $this->assertEquals($project3->id, $export->collection()[2]['choice_3']);
            $this->assertEquals($project3->title, $export->collection()[2]['choice_3_title']);
            $this->assertEquals($project4->id, $export->collection()[2]['choice_4']);
            $this->assertEquals($project4->title, $export->collection()[2]['choice_4_title']);
            $this->assertEquals($project5->id, $export->collection()[2]['choice_5']);
            $this->assertEquals($project5->title, $export->collection()[2]['choice_5_title']);

            return true;
        });
    }

    /** @test */
    public function an_admin_can_export_the_list_of_postgrads_as_a_csv(): void
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $student1 = User::factory()->student()->create(['is_staff' => false, 'surname' => 'Aaa']);
        $student2 = User::factory()->student()->create(['is_staff' => false, 'surname' => 'Bbb']);
        $course->students()->saveMany([$student1, $student2]);
        $project1 = create(Project::class, ['category' => 'postgrad']);
        $project2 = create(Project::class, ['category' => 'postgrad']);
        $project3 = create(Project::class, ['category' => 'postgrad']);
        $project4 = create(Project::class, ['category' => 'postgrad']);
        $project5 = create(Project::class, ['category' => 'postgrad']);
        $project1->addAndAccept($student1);
        $student2->projects()->sync([
            $project1->id => ['choice' => 1],
            $project2->id => ['choice' => 2],
            $project3->id => ['choice' => 3],
            $project4->id => ['choice' => 4],
            $project5->id => ['choice' => 5],
        ]);

        $response = $this->actingAs($admin)->get(route('export.postgrad', 'csv'));

        $response->assertOk();
        Excel::assertDownloaded('uog_postgrad_project_students.csv', function (StudentsExport $export) use ($student1, $student2, $project1, $project2, $project3, $project4, $project5) {
            //3 rows, 2 students + headers
            $this->assertCount(3, $export->collection());

            $this->assertEquals($student1->matric, $export->collection()[1]['matric']);
            $this->assertEquals($student1->surname, $export->collection()[1]['surname']);
            $this->assertEquals($student1->forenames, $export->collection()[1]['forenames']);
            $this->assertEquals($student1->programme->plan_code, $export->collection()[1]['plan_code']);
            $this->assertEquals($project1->id, $export->collection()[1]['choice_1']);
            $this->assertEquals($project1->title, $export->collection()[1]['choice_1_title']);
            $this->assertEquals('', $export->collection()[1]['choice_2']);
            $this->assertEquals('', $export->collection()[1]['choice_2_title']);
            $this->assertEquals('', $export->collection()[1]['choice_3']);
            $this->assertEquals('', $export->collection()[1]['choice_3_title']);
            $this->assertEquals('', $export->collection()[1]['choice_4']);
            $this->assertEquals('', $export->collection()[1]['choice_4_title']);
            $this->assertEquals('', $export->collection()[1]['choice_5']);
            $this->assertEquals('', $export->collection()[1]['choice_5_title']);

            $this->assertEquals($student2->matric, $export->collection()[2]['matric']);
            $this->assertEquals($student2->surname, $export->collection()[2]['surname']);
            $this->assertEquals($student2->forenames, $export->collection()[2]['forenames']);
            $this->assertEquals($student2->programme->plan_code, $export->collection()[2]['plan_code']);
            $this->assertEquals($project1->id, $export->collection()[2]['choice_1']);
            $this->assertEquals($project1->title, $export->collection()[2]['choice_1_title']);
            $this->assertEquals($project2->id, $export->collection()[2]['choice_2']);
            $this->assertEquals($project2->title, $export->collection()[2]['choice_2_title']);
            $this->assertEquals($project3->id, $export->collection()[2]['choice_3']);
            $this->assertEquals($project3->title, $export->collection()[2]['choice_3_title']);
            $this->assertEquals($project4->id, $export->collection()[2]['choice_4']);
            $this->assertEquals($project4->title, $export->collection()[2]['choice_4_title']);
            $this->assertEquals($project5->id, $export->collection()[2]['choice_5']);
            $this->assertEquals($project5->title, $export->collection()[2]['choice_5_title']);

            return true;
        });
    }

    /** @test */
    public function an_admin_can_export_the_list_of_postgrads_as_an_xlsx(): void
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $student1 = User::factory()->student()->create(['is_staff' => false, 'surname' => 'Aaa']);
        $student2 = User::factory()->student()->create(['is_staff' => false, 'surname' => 'Bbb']);
        $course->students()->saveMany([$student1, $student2]);
        $project1 = create(Project::class, ['category' => 'postgrad']);
        $project2 = create(Project::class, ['category' => 'postgrad']);
        $project3 = create(Project::class, ['category' => 'postgrad']);
        $project4 = create(Project::class, ['category' => 'postgrad']);
        $project5 = create(Project::class, ['category' => 'postgrad']);
        $project1->addAndAccept($student1);
        $student2->projects()->sync([
            $project1->id => ['choice' => 1],
            $project2->id => ['choice' => 2],
            $project3->id => ['choice' => 3],
            $project4->id => ['choice' => 4],
            $project5->id => ['choice' => 5],
        ]);

        $response = $this->actingAs($admin)->get(route('export.postgrad', 'xlsx'));

        $response->assertOk();
        Excel::assertDownloaded('uog_postgrad_project_students.xlsx', function (StudentsExport $export) use ($student1, $student2, $project1, $project2, $project3, $project4, $project5) {
            //3 rows, 2 students + headers
            $this->assertCount(3, $export->collection());

            $this->assertEquals($student1->matric, $export->collection()[1]['matric']);
            $this->assertEquals($student1->surname, $export->collection()[1]['surname']);
            $this->assertEquals($student1->forenames, $export->collection()[1]['forenames']);
            $this->assertEquals($student1->programme->plan_code, $export->collection()[1]['plan_code']);
            $this->assertEquals($project1->id, $export->collection()[1]['choice_1']);
            $this->assertEquals($project1->title, $export->collection()[1]['choice_1_title']);
            $this->assertEquals('', $export->collection()[1]['choice_2']);
            $this->assertEquals('', $export->collection()[1]['choice_2_title']);
            $this->assertEquals('', $export->collection()[1]['choice_3']);
            $this->assertEquals('', $export->collection()[1]['choice_3_title']);
            $this->assertEquals('', $export->collection()[1]['choice_4']);
            $this->assertEquals('', $export->collection()[1]['choice_4_title']);
            $this->assertEquals('', $export->collection()[1]['choice_5']);
            $this->assertEquals('', $export->collection()[1]['choice_5_title']);

            $this->assertEquals($student2->matric, $export->collection()[2]['matric']);
            $this->assertEquals($student2->surname, $export->collection()[2]['surname']);
            $this->assertEquals($student2->forenames, $export->collection()[2]['forenames']);
            $this->assertEquals($student2->programme->plan_code, $export->collection()[2]['plan_code']);
            $this->assertEquals($project1->id, $export->collection()[2]['choice_1']);
            $this->assertEquals($project1->title, $export->collection()[2]['choice_1_title']);
            $this->assertEquals($project2->id, $export->collection()[2]['choice_2']);
            $this->assertEquals($project2->title, $export->collection()[2]['choice_2_title']);
            $this->assertEquals($project3->id, $export->collection()[2]['choice_3']);
            $this->assertEquals($project3->title, $export->collection()[2]['choice_3_title']);
            $this->assertEquals($project4->id, $export->collection()[2]['choice_4']);
            $this->assertEquals($project4->title, $export->collection()[2]['choice_4_title']);
            $this->assertEquals($project5->id, $export->collection()[2]['choice_5']);
            $this->assertEquals($project5->title, $export->collection()[2]['choice_5_title']);

            return true;
        });
    }
}
