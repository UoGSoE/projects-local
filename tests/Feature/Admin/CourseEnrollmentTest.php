<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class CourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_see_the_page_to_import_students_to_a_course()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);

        $response = $this->actingAs($admin)->get(route('admin.course.enrollment', $course->id));

        $response->assertSuccessful();
        $response->assertSee($course->title);
    }

    /** @test */
    public function an_admin_can_import_a_spreadsheet_of_students_who_are_on_a_course()
    {
        $this->withoutExceptionHandling();
        // given we have an admin and a course
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $this->assertEquals(0, $course->students()->count());
        $filename = './tests/Feature/data/course_students.xlsx';
        Activity::all()->each->delete();

        // and we upload a test spreadsheet with two students details
        $response = $this->actingAs($admin)->post(route('admin.course.enroll', $course->id), [
            'sheet' => new UploadedFile($filename, 'course_students.xlsx', 'application/octet-stream', null, true),
        ]);

        // the course should have two students attached
        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertEquals(5, $course->students()->count());
        $student = $course->students()->where('username', '=', '2383616s')->first();
        $this->assertEquals('2383616s@student.gla.ac.uk', $student->email);
        $this->assertEquals('Sham', $student->surname);
        $this->assertEquals('Allan', $student->forenames);
        $this->assertTrue($student->isStudent());

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals("Enrolled students onto {$course->code}", $logs[0]->description);
    }

    /** @test */
    public function when_admin_imports_the_spreadsheet_any_existing_students_on_the_course_are_kept()
    {
        // given we have an admin, a student and a course with that student on it
        $admin = create(User::class, ['is_admin' => true]);
        $student = create(User::class, ['username' => 'EXISTINGSTUDENT', 'is_staff' => false]);
        $course = create(Course::class);
        $course->students()->save($student);
        $this->assertEquals(1, $course->students()->count());
        $filename = './tests/Feature/data/course_students.xlsx';

        // and we upload a test spreadsheet with new students details
        $response = $this->actingAs($admin)->post(route('admin.course.enroll', $course->id), [
            'sheet' => new UploadedFile($filename, 'course_students.xlsx', 'application/octet-stream', UPLOAD_ERR_OK, true),
        ]);

        // the course should have the new students students
        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertEquals(6, $course->students()->count());
        // and the previously enrolled student too
        $this->assertDatabaseHas('users', ['username' => 'EXISTINGSTUDENT']);
    }

    /** @test */
    public function students_can_be_identified_by_a_full_guid_or_guessed_at_from_a_matric_number()
    {
        // given we have an admin, a student and a course with that student on it
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $filename = './tests/Feature/data/course_students_with_guids.xlsx';

        // and we upload a test spreadsheet with new students details
        $response = $this->actingAs($admin)->post(route('admin.course.enroll', $course->id), [
            'sheet' => new UploadedFile($filename, 'course_students.xlsx', 'application/octet-stream', UPLOAD_ERR_OK, true),
        ]);

        // the course should have the new students students
        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertEquals(5, $course->students()->count());
        // and check their usernames have the correct manual guid or guessed at ones
        $this->assertDatabaseHas('users', ['username' => '2349804q']);
        $this->assertDatabaseHas('users', ['username' => '2383616s']);
        $this->assertDatabaseHas('users', ['username' => '2352733p']);
        $this->assertDatabaseHas('users', ['username' => '2354455v']);
        $this->assertDatabaseHas('users', ['username' => '2279698z']);
    }
}
