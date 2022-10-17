<?php

namespace Tests\Feature\Admin;

use App\Mail\AcceptedOntoProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

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
    public function an_admin_can_unaccept_any_student_on_a_given_project()
    {
        Mail::fake();
        // given we have a an undergrad project with two accepted students
        $admin = create(User::class, ['is_admin' => true]);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['category' => 'undergrad']);
        $student1->projects()->sync([$project->id => ['choice' => 1, 'is_accepted' => true]]);
        $student2->projects()->sync([$project->id => ['choice' => 1, 'is_accepted' => true]]);
        $this->assertTrue($student1->isAccepted());
        $this->assertTrue($student2->isAccepted());

        // and when we submit the form with student2 missing
        $response = $this->actingAs($admin)->post(route('project.accept_students', $project->id), [
            'students' => [$student1->id],
        ]);

        // the student2 should be unaccepted, and student1 should still be accepted
        $response->assertRedirect(route('project.show', $project->id));
        $response->assertSessionHas('success');
        $this->assertTrue($student1->fresh()->isAccepted());
        $this->assertFalse($student2->fresh()->isAccepted());
    }

    /** @test */
    public function an_admin_can_manually_add_and_accept_any_student_on_a_given_project()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        // given we have a an undergrad project and a student
        $admin = create(User::class, ['is_admin' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['category' => 'undergrad']);

        // and when we submit the form with that student
        $response = $this->actingAs($admin)->post(route('admin.project.add_student', $project->id), [
            'student_id' => $student->id,
        ]);

        // the student should be on that project and accepted
        $response->assertSuccessful();
        $response->assertSessionHas('success');
        $this->assertTrue($project->students()->first()->is($student));
        $this->assertTrue($project->students()->first()->isAccepted());
        // Mail::assertQueued(AcceptedOntoProject::class, function ($mail) use ($project, $student) {
        //     return $mail->hasTo($student->email);
        // });
    }

    /** @test */
    public function admins_can_see_the_bulk_acceptance_pages()
    {
        // given we have an admin
        $admin = create(User::class, ['is_admin' => true]);
        // and some projects
        $ugProject1 = create(Project::class, ['category' => 'undergrad']);
        $ugProject2 = create(Project::class, ['category' => 'undergrad']);
        $pgProject1 = create(Project::class, ['category' => 'postgrad']);
        $pgProject2 = create(Project::class, ['category' => 'postgrad']);
        // and some students
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $student3 = create(User::class, ['is_staff' => false]);
        $student4 = create(User::class, ['is_staff' => false]);
        // and the students have chosen projects
        $student1->projects()->sync([$ugProject1->id => ['choice' => 1]]);
        $student2->projects()->sync([$ugProject2->id => ['choice' => 2]]);
        $student3->projects()->sync([$pgProject1->id => ['choice' => 2]]);
        $student4->projects()->sync([$pgProject2->id => ['choice' => 1]]);

        // when we view the undergrad bulk acceptance page
        $response = $this->actingAs($admin)->get(route('admin.student.choices', 'undergrad'));

        // we should only see the undergrad projects & students
        $response->assertSuccessful();
        $response->assertSee($ugProject1->title);
        $response->assertSee($ugProject2->title);
        $response->assertSee($ugProject1->owner->full_name);
        $response->assertDontSee($pgProject1->title);
        $response->assertDontSee($pgProject2->title);
        $response->assertSee($student1->full_name);
        $response->assertSee($student2->full_name);
        $response->assertDontSee($student3->full_name);

        // when we view the postgrad bulk acceptance page
        $response = $this->actingAs($admin)->get(route('admin.student.choices', 'postgrad'));

        // we should only see the postgrad projects & students
        $response->assertSuccessful();
        $response->assertDontSee($ugProject1->title);
        $response->assertDontSee($ugProject2->title);
        $response->assertSee($pgProject1->title);
        $response->assertSee($pgProject2->title);
        $response->assertDontSee($student1->full_name);
        $response->assertDontSee($student2->full_name);
        $response->assertSee($student3->full_name);
        $response->assertSee($student4->full_name);
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
                $student1->id => $project1->id,
                $student2->id => $project2->id,
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
        // Mail::assertQueued(AcceptedOntoProject::class, 2);
    }
}
