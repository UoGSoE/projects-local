<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Project;
use Tests\TestCase;
use App\Mail\AcceptedOntoProject;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AcceptStudentsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function when_an_admin_views_a_project_they_can_always_see_the_form_to_accept_students()
    {
        // we have an admin, a student and an undergrad project
        $admin = create(User::class, ['is_admin' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['category' => 'undergrad']);
        // and the student has made it their 2nd preference (so shouldn't be on the form for normal staff)
        $student->projects()->sync([$project->id => ['choice' => 2]]);

        // when we view the project
        $response = $this->actingAs($admin)->get(route('project.show', $project->id));

        // ... it should work ;-)
        $response->assertSuccessful();
    }

    /** @test */
    public function an_admin_can_accept_any_student_on_a_given_project()
    {
        Mail::fake();
        // given we have a an undergrad project and a student has applied as their 2nd choice
        $admin = create(User::class, ['is_admin' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['category' => 'undergrad']);
        $student->projects()->sync([$project->id => ['choice' => 2]]);
        $this->assertFalse($project->students()->first()->isAccepted());

        // when we view the project
        $response = $this->actingAs($admin)->get(route('project.show', $project->id));

        // we get the page view and the right project
        $response->assertSuccessful();
        $this->assertTrue($response->data('project')->is($project));

        // and when we submit the form
        $response = $this->actingAs($admin)->post(route('project.accept_students', $project->id), [
            'students' => [$student->id],
        ]);

        // the student should be accepted
        $response->assertRedirect(route('project.show', $project->id));
        $response->assertSessionHas('success');
        $this->assertTrue($project->students()->first()->isAccepted());
    }

    /** @test */
    public function an_admin_can_bulk_accept_students_onto_projects()
    {
        Mail::fake();
        // given we have an admin
        $admin = create(User::class, ['is_admin' => true]);
        // and some projects
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        // and some students
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $student3 = create(User::class, ['is_staff' => false]);
        // and the students have chosen projects
        $student1->projects()->sync([$project1->id => ['choice' => 1]]);
        $student2->projects()->sync([$project2->id => ['choice' => 2]]);
        $student3->projects()->sync([$project2->id => ['choice' => 2]]);

        // when the admin does a bulk accept post
        $response = $this->actingAs($admin)->post(route('project.bulk_accept'), [
            'students' => [
                [$student1->id => $project1->id],
                [$student2->id => $project2->id],
            ],
        ]);

        // we should see the correct students have now been accepted onto the correct projeects
        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertTrue($student1->isAccepted());
        $this->assertTrue($student2->isAccepted());
        $this->assertFalse($student3->isAccepted());
        $this->assertEquals($project1->id, $student1->projects()->first()->id);
        $this->assertEquals($project2->id, $student2->projects()->first()->id);
        // and they have been sent acceptance emails
        Mail::assertQueued(AcceptedOntoProject::class, 2);
    }
}
