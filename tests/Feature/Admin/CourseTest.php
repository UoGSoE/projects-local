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
        $course->students()->sync([$student1->id, $student3->id]);

        $response = $this->actingAs($admin)->get(route('admin.course.show', $course->id));

        $response->assertSuccessful();
        $response->assertSee('Course ' . $course->code);
        $response->assertSee($course->title);
        $response->assertSee($student1->full_name);
        $response->assertSee($student3->full_name);
    }

    /** @test */
    public function admins_can_create_a_new_course()
    {
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.course.store'), [
            'title' => "A COURSE",
            'code' => "ENG9999",
        ]);

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $course = Course::first();
        $this->assertEquals('A COURSE', $course->title);
        $this->assertEquals('ENG9999', $course->code);
    }

    /** @test */
    public function a_title_and_a_unique_code_are_required_to_create_a_new_course()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $existingCourse = create(Course::class, [
            'title' => 'A COURSE',
            'code' => 'ENG9999'
        ]);

        $response = $this->actingAs($admin)->post(route('admin.course.store'), [
            'title' => "",
            'code' => $existingCourse->code,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('title');
        $response->assertSessionHasErrors('code');
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
        ]);

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertEquals('A COURSE', $existingCourse->fresh()->title);
        $this->assertEquals('ENG9999', $existingCourse->fresh()->code);
    }

    /** @test */
    public function a_title_and_a_unique_code_are_required_when_updating_a_course()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $otherCourse = create(Course::class, [
            'title' => 'A COURSE',
            'code' => 'ENG9999'
        ]);

        $response = $this->actingAs($admin)->post(route('admin.course.update', $course->id), [
            'title' => "",
            'code' => $otherCourse->code,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('title');
        $response->assertSessionHasErrors('code');
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
}
