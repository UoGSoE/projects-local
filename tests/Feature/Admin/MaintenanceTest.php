<?php

namespace Tests\Feature\Admin;

use App\Course;
use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

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
        // we have an undergrad, postgrad and 'mystery' student
        $undergrad = create(User::class, ['is_staff' => false]);
        $postgrad = create(User::class, ['is_staff' => false]);
        $mysteryGrad = create(User::class, ['is_staff' => false]);
        // and we have an undergrad & postgrad course & project
        $ugradCourse = create(Course::class, ['category' => 'undergrad']);
        $pgradCourse = create(Course::class, ['category' => 'postgrad']);
        $ugradProject = create(Project::class, ['category' => 'undergrad']);
        $pgradProject = create(Project::class, ['category' => 'postgrad']);
        // the undergrad student is on the undergrad project
        $undergrad->projects()->sync([$ugradProject->id => ['choice' => 1]]);
        // the postgrad and mystery student are on the postgrad project
        $postgrad->projects()->sync([$pgradProject->id => ['choice' => 1]]);
        $mysteryGrad->projects()->sync([$pgradProject->id => ['choice' => 1]]);
        // the undergrad student is on an undergrad course
        $undergrad->course()->associate($ugradCourse);
        $undergrad->save();
        // the postgrad student is on a postgrad course
        $postgrad->course()->associate($pgradCourse);
        $postgrad->save();
        // (the mystery student is _not_ on a course - for instance been manually added by admins)

        Activity::all()->each->delete();

        // when we make the call to remove all undergrads
        $response = $this->actingAs($admin)->delete(route('students.remove_undergrad'));

        // the undergrad student should be gone but the postgrad & mystery student should remain
        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertDatabaseMissing('users', ['id' => $undergrad->id]);
        $this->assertDatabaseHas('users', ['id' => $postgrad->id]);
        $this->assertDatabaseHas('users', ['id' => $mysteryGrad->id]);

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals('Removed all undergrad students', $logs[0]->description);

        Activity::all()->each->delete();

        // when we call to remove all postgrads
        $response = $this->actingAs($admin)->delete(route('students.remove_postgrad'));

        // the postgrad & mystery students should be gone too
        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertDatabaseMissing('users', ['id' => $postgrad->id]);
        $this->assertDatabaseMissing('users', ['id' => $mysteryGrad->id]);

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals('Removed all postgrad students', $logs[0]->description);
    }

    /** @test */
    public function an_admin_can_delete_specific_staff_or_student()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $staff = create(User::class, ['is_staff' => true]);

        $response = $this->actingAs($admin)->delete(route('admin.user.delete', $staff->id));

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
        $this->assertDatabaseHas('users', ['id' => $student->id]);

        $response = $this->actingAs($admin)->delete(route('admin.user.delete', $student->id));

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertDatabaseMissing('users', ['id' => $student->id]);
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

    /** @test */
    public function regular_users_cant_delete_anything()
    {
        $user = create(User::class);
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $student = create(User::class, ['is_staff' => false]);
        $staff = create(User::class, ['is_staff' => true]);

        $response = $this->actingAs($user)->delete(route('course.remove_students', $course->id));
        $response->assertStatus(302);
        $response->assertRedirect('/');

        $response = $this->actingAs($user)->delete(route('students.remove_undergrad'));
        $response->assertStatus(302);
        $response->assertRedirect('/');

        $response = $this->actingAs($user)->delete(route('students.remove_postgrad'));
        $response->assertStatus(302);
        $response->assertRedirect('/');

        $response = $this->actingAs($user)->delete(route('admin.user.delete', $staff->id));
        $response->assertStatus(302);
        $response->assertRedirect('/');

        $response = $this->actingAs($user)->delete(route('students.remove_all'));
        $response->assertStatus(302);
        $response->assertRedirect('/');
    }
}
