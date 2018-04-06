<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Course;
use App\Project;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MaintenanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_clear_all_students_from_a_given_course()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $students = create(User::class, ['is_staff' => false], 3);
        $course->students()->saveMany($students);

        $response = $this->actingAs($admin)->delete(route('course.remove_students', $course->id));

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertCount(0, $course->students);
    }

    /** @test */
    public function an_admin_can_clear_all_postgrad_or_undergrad_students()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $undergrad = create(User::class, ['is_staff' => false]);
        $postgrad = create(User::class, ['is_staff' => false]);
        $ugradCourse = create(Course::class, ['category' => 'undergrad']);
        $pgradCourse = create(Course::class, ['category' => 'postgrad']);
        $ugradProject = create(Project::class, ['category' => 'undergrad']);
        $pgradProject = create(Project::class, ['category' => 'postgrad']);
        $undergrad->projects()->sync([$ugradProject->id => ['choice' => 1]]);
        $postgrad->projects()->sync([$pgradProject->id => ['choice' => 1]]);
        $undergrad->course()->associate($ugradCourse);
        $undergrad->save();
        $postgrad->course()->associate($pgradCourse);
        $postgrad->save();

        $response = $this->actingAs($admin)->delete(route('students.remove_undergrads'));

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertDatabaseMissing('users', ['id' => $undergrad->id]);
        $this->assertDatabaseHas('users', ['id' => $postgrad->id]);

        $response = $this->actingAs($admin)->delete(route('students.remove_postgrads'));

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertDatabaseMissing('users', ['id' => $postgrad->id]);
    }

    /** @test */
    public function an_admin_can_clear_all_students()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $staff = create(User::class, ['is_staff' => true]);

        $response = $this->actingAs($admin)->delete(route('students.remove_all'));

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertDatabaseMissing('users', ['id' => $student1->id]);
        $this->assertDatabaseMissing('users', ['id' => $student2->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseHas('users', ['id' => $staff->id]);
    }
}
