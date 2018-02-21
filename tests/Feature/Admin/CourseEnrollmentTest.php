<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Course;
use App\Project;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_import_a_spreadsheet_of_students_who_are_on_a_course()
    {
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

    /** @test */
    public function when_admin_imports_the_spreadsheet_any_existing_students_on_the_course_are_deleted()
    {
        // given we have an admin, a student and a course with that student on it
        $admin = create(User::class, ['is_admin' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $course = create(Course::class);
        $course->students()->save($student);
        $this->assertEquals(1, $course->students()->count());
        $filename = './tests/Feature/data/course_students.xlsx';

        // and we upload a test spreadsheet with two students details
        $response = $this->actingAs($admin)->post(route('admin.course.enroll', $course->id), [
            'sheet' => new UploadedFile($filename, 'course_students.xlsx', 'application/octet-stream', filesize($filename), UPLOAD_ERR_OK, true),
        ]);

        // the course should have two students attached
        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertEquals(2, $course->students()->count());
        // and the previously enrolled student should be gone
        $this->assertDatabaseMissing('users', ['id' => $student->id]);
    }

    /** @test */
    public function when_admin_imports_the_spreadsheet_any_existing_project_choices_for_deleted_students_are_cleared()
    {
        // given we have an admin, a student, a project and the student has chosen the project
        $admin = create(User::class, ['is_admin' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $course = create(Course::class);
        $course->students()->save($student);
        $project = create(Project::class);
        $project->courses()->sync([$course->id]);
        $project->students()->sync([$student->id => ['choice' => 1]]);

        $this->assertEquals(1, $course->students()->count());
        $this->assertDatabaseHas('project_students', ['student_id' => $student->id]);

        $filename = './tests/Feature/data/course_students.xlsx';

        // and we upload a test spreadsheet with two students details
        $response = $this->actingAs($admin)->post(route('admin.course.enroll', $course->id), [
            'sheet' => new UploadedFile($filename, 'course_students.xlsx', 'application/octet-stream', filesize($filename), UPLOAD_ERR_OK, true),
        ]);

        // the course should have two students attached
        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertEquals(2, $course->students()->count());
        // the original student should be deleted
        $this->assertDatabaseMissing('users', ['id' => $student->id]);
        // and their choices should be gone
        $this->assertDatabaseMissing('project_students', ['student_id' => $student->id]);
    }
}
