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
}
