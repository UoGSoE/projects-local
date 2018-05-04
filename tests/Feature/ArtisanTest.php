<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Course;
use App\Project;

class ArtisanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function we_can_give_admin_rights_to_an_existing_user()
    {
        $user1 = create(User::class, ['is_admin' => false]);
        $user2 = create(User::class, ['is_admin' => false]);
        $this->assertFalse($user1->isAdmin());
        $this->assertFalse($user2->isAdmin());

        \Artisan::call('projects:makeadmin', ['username' => $user1->username]);

        $this->assertTrue($user1->fresh()->isAdmin());
        $this->assertFalse($user2->fresh()->isAdmin());
    }

    /** @test */
    public function we_can_remove_any_students_who_arent_on_a_course_or_a_project()
    {
        // eg, students who logged in by mistake or for a nosey

        $course = create(Course::class);
        $project = create(Project::class);
        $staff = create(User::class, ['is_staff' => true]);
        $legitStudent1 = create(User::class, ['is_staff' => false, 'course_id' => $course->id]);
        $legitStudent2 = create(User::class, ['is_staff' => false]);
        $project->students()->sync([$legitStudent2->id => ['choice' => 1]]);
        $pointlessStudent = create(User::class, ['is_staff' => false]);

        \Artisan::call('projects:deleteunusedstudents');

        $this->assertDatabaseHas('users', ['id' => $staff->id]);
        $this->assertDatabaseHas('users', ['id' => $legitStudent1->id]);
        $this->assertDatabaseHas('users', ['id' => $legitStudent2->id]);
        $this->assertDatabaseMissing('users', ['id' => $pointlessStudent->id]);
    }
}
