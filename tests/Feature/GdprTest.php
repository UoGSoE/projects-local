<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Course;
use App\Project;

class GdprTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admins_can_export_all_data_about_a_user_as_json()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_staff' => true, 'is_admin' => true]);
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $course = create(Course::class);
        $course->students()->save($student);
        $project = create(Project::class, ['staff_id' => $staff->id]);
        $project->students()->sync([$student->id => ['is_accepted' => true, 'choice' => 1]]);

        $response = $this->actingAs($admin)->get(route('gdpr.export.user', $student->id));

        $response->assertSuccessful();
        $response->assertJson([
            'data' => [
                'username' => $student->username,
                'surname' => $student->surname,
                'forenames' => $student->forenames,
                'email' => $student->email,
                'course' => $student->course->code,
                'projects' => [
                    0 => [
                        'title' => $project->title,
                        'choice' => 1,
                        'accepted' => true,
                    ]
                ]
            ]
        ]);

        $response = $this->actingAs($admin)->get(route('gdpr.export.user', $staff->id));

        $response->assertSuccessful();
        $response->assertJson([
            'data' => [
                'username' => $staff->username,
                'surname' => $staff->surname,
                'forenames' => $staff->forenames,
                'email' => $staff->email,
                'projects' => [
                    0 => [
                        'title' => $project->title,
                        'category' => $project->category,
                        'active' => $project->is_active,
                    ]
                ]
            ]
        ]);
    }
}
