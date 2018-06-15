<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Course;
use App\Project;
use App\Programme;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function regular_users_cant_view_a_course()
    {
        $user = create(User::class);
        $course = create(Course::class);

        $response = $this->actingAs($user)->get(route('admin.course.show', $course->id));

        $response->assertRedirect('/');
    }

    /** @test */
    public function admins_can_view_a_course()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $student3 = create(User::class, ['is_staff' => false]);
        $course->students()->saveMany([$student1, $student3]);

        $response = $this->actingAs($admin)->get(route('admin.course.show', $course->id));

        $response->assertSuccessful();
        $response->assertSee('Course ' . $course->code);
        $response->assertSee($course->title);
        $response->assertSee($course->application_deadline->format('d/m/Y'));
        $response->assertSee($student1->full_name);
        $response->assertSee($student3->full_name);
    }

    /** @test */
    public function admins_can_see_a_list_of_all_courses()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = create(Course::class);
        $course2 = create(Course::class);

        $response = $this->actingAs($admin)->get(route('admin.course.index'));

        $response->assertSuccessful();
        $response->assertSee($course1->title);
        $response->assertSee($course2->title);
        $response->assertSee($course1->application_deadline->format('d/m/Y'));
        $response->assertSee($course2->application_deadline->format('d/m/Y'));
    }

    /** @test */
    public function admins_can_see_the_page_to_create_a_new_course()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.course.create'));

        $response->assertSuccessful();
        $response->assertSee('Create new course');
    }

    /** @test */
    public function admins_can_create_a_new_course()
    {
        $this->refreshDatabase();
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.course.store'), [
            'title' => "A COURSE",
            'code' => "ENG9999",
            'category' => 'undergrad',
            'application_deadline' => now()->addMonths(3)->format('d/m/Y'),
        ]);

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $course = Course::first();

        $this->assertEquals('A COURSE', $course->title);
        $this->assertEquals('ENG9999', $course->code);
        $this->assertEquals(now()->addMonths(3)->format('d/m/Y 23:59'), $course->application_deadline->format('d/m/Y H:i'));
    }

    /** @test */
    public function a_title_and_application_deadline_and_a_unique_code_are_required_to_create_a_new_course()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $existingCourse = create(Course::class, [
            'title' => 'A COURSE',
            'code' => 'ENG9999',
            'category' => 'undergrad',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.course.store'), [
            'title' => "",
            'code' => $existingCourse->code,
            'category' => 'undergrad',
            'application_deadline' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('title');
        $response->assertSessionHasErrors('code');
        $response->assertSessionHasErrors('application_deadline');
        $this->assertEquals('A COURSE', $existingCourse->fresh()->title);
        $this->assertEquals('ENG9999', $existingCourse->fresh()->code);
    }

    /** @test */
    public function admins_can_update_an_existing_course()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $existingCourse = create(Course::class);

        $response = $this->actingAs($admin)->post(route('admin.course.update', $existingCourse->id), [
            'title' => "A COURSE",
            'code' => "ENG9999",
            'category' => 'undergrad',
            'application_deadline' => now()->addMonths(3)->format('d/m/Y'),
        ]);

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertEquals('A COURSE', $existingCourse->fresh()->title);
        $this->assertEquals('ENG9999', $existingCourse->fresh()->code);
        $this->assertEquals(now()->addMonths(3)->format('d/m/Y'), $existingCourse->fresh()->application_deadline->format('d/m/Y'));
    }

    /** @test */
    public function a_title_and_an_application_deadline_and_a_unique_code_are_required_when_updating_a_course()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $otherCourse = create(Course::class, [
            'title' => 'A COURSE',
            'code' => 'ENG9999',
            'category' => 'undergrad',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.course.update', $course->id), [
            'title' => "",
            'code' => $otherCourse->code,
            'category' => 'undergrad',
            'application_deadline' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('title');
        $response->assertSessionHasErrors('code');
        $response->assertSessionHasErrors('application_deadline');
    }

    /** @test */
    public function an_admin_can_delete_a_course()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);

        $response = $this->actingAs($admin)->delete(route('admin.course.destroy', $course->id));

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    /** @test */
    public function deleting_a_course_removes_all_students_who_were_on_it()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        $student1 = create(User::class, ['course_id' => $course1->id]);
        $student2 = create(User::class, ['course_id' => $course2->id]);

        $response = $this->actingAs($admin)->delete(route('admin.course.destroy', $course2->id));

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('courses', ['id' => $course2->id]);
        $this->assertDatabaseMissing('users', ['id' => $student2->id]);
        $this->assertDatabaseHas('users', ['id' => $student1->id]);
    }
}
