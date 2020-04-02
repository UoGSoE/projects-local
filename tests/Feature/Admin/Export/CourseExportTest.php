<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature\Admin\Export;

use App\Course;
use App\Exports\CoursesExport;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class CourseExportTest extends TestCase
{
    /** @test */
    public function an_admin_can_download_a_csv_of_all_courses()
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = create(Course::class, ['code' => 'ENG1234']);
        $course2 = create(Course::class, ['code' => 'ENG9999']);

        $response = $this->actingAs($admin)->get(route('export.courses', ['format' => 'csv']));

        $response->assertOk();
        Excel::assertDownloaded('uog_courses.csv', function (CoursesExport $export) use ($course1, $course2) {
            //3 rows, 2 courses + headers
            $this->assertCount(3, $export->collection());

            $this->assertEquals($course1->code, $export->collection()[1]['code']);
            $this->assertEquals($course1->title, $export->collection()[1]['title']);
            $this->assertEquals($course1->type, $export->collection()[1]['type']);
            $this->assertEquals($course1->application_deadline->format('d/m/Y'), $export->collection()[1]['application_deadline']);
            $this->assertEquals($course1->projects->count(), $export->collection()[1]['projects_count']);
            $this->assertEquals($course1->students->count(), $export->collection()[1]['students_count']);

            $this->assertEquals($course2->code, $export->collection()[2]['code']);
            $this->assertEquals($course2->title, $export->collection()[2]['title']);
            $this->assertEquals($course2->type, $export->collection()[2]['type']);
            $this->assertEquals($course2->application_deadline->format('d/m/Y'), $export->collection()[2]['application_deadline']);
            $this->assertEquals($course2->projects->count(), $export->collection()[2]['projects_count']);
            $this->assertEquals($course2->students->count(), $export->collection()[2]['students_count']);

            return true;
        });
    }

    /** @test */
    public function an_admin_can_download_an_xlsx_of_all_courses()
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = create(Course::class, ['code' => 'ENG1234']);
        $course2 = create(Course::class, ['code' => 'ENG9999']);

        $response = $this->actingAs($admin)->get(route('export.courses', ['format' => 'xlsx']));

        $response->assertOk();
        Excel::assertDownloaded('uog_courses.xlsx', function (CoursesExport $export) use ($course1, $course2) {
            //3 rows, 2 courses + headers
            $this->assertCount(3, $export->collection());

            $this->assertEquals($course1->code, $export->collection()[1]['code']);
            $this->assertEquals($course1->title, $export->collection()[1]['title']);
            $this->assertEquals($course1->type, $export->collection()[1]['type']);
            $this->assertEquals($course1->application_deadline->format('d/m/Y'), $export->collection()[1]['application_deadline']);
            $this->assertEquals($course1->projects->count(), $export->collection()[1]['projects_count']);
            $this->assertEquals($course1->students->count(), $export->collection()[1]['students_count']);

            $this->assertEquals($course2->code, $export->collection()[2]['code']);
            $this->assertEquals($course2->title, $export->collection()[2]['title']);
            $this->assertEquals($course2->type, $export->collection()[2]['type']);
            $this->assertEquals($course2->application_deadline->format('d/m/Y'), $export->collection()[2]['application_deadline']);
            $this->assertEquals($course2->projects->count(), $export->collection()[2]['projects_count']);
            $this->assertEquals($course2->students->count(), $export->collection()[2]['students_count']);

            return true;
        });
    }
}
