<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Course;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_import_a_spreadsheet_of_students_who_are_on_a_course()
    {
        $this->withoutExceptionHandling();
        // given we have an admin and a course
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $this->assertEquals(0, $course->students()->count());
        $filename = './tests/Feature/data/course_students.xlsx';

        // and we upload a test spreadsheet with two students details
        $response = $this->actingAs($admin)->post(route('admin.course.enroll', $course->id), [
            'sheet' => new UploadedFile($filename, 'course_students.xlsx', 'application/octet-stream', filesize($filename), UPLOAD_ERR_OK, true),
        ]);

        // the course should have two students attached
        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertEquals(2, $course->students()->count());
    }
}
