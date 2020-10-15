<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

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
        $studentOnACourse = create(User::class, ['is_staff' => false, 'course_id' => $course->id]);
        $studentOnAProject = create(User::class, ['is_staff' => false]);
        $project->students()->sync([$studentOnAProject->id => ['choice' => 1]]);
        $pointlessStudent = create(User::class, ['is_staff' => false]);

        \Artisan::call('projects:deleteunusedstudents');

        $this->assertDatabaseHas('users', ['id' => $staff->id]);
        $this->assertDatabaseHas('users', ['id' => $studentOnACourse->id]);
        $this->assertDatabaseHas('users', ['id' => $studentOnAProject->id]);
        $this->assertDatabaseMissing('users', ['id' => $pointlessStudent->id]);
    }
}
