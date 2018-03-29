<?php

namespace Tests\Feature\Staff;

use App\User;
use App\Course;
use App\Project;
use Tests\TestCase;
use App\Mail\AcceptedOntoProject;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AcceptingStudentsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function staff_can_accept_first_choice_students_for_undergrad_projects()
    {
        Mail::fake();
        // given we have a member of staff with an undergrad project and a student has applied as their 1st choice
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $student->projects()->sync([$project->id => ['choice' => 1]]);
        $this->assertFalse($project->students()->first()->isAccepted());

        // when we view the project
        $response = $this->actingAs($staff)->get(route('project.show', $project->id));

        // it should view ok with the right data
        $response->assertSuccessful();
        $this->assertTrue($response->data('project')->is($project));

        // and when we submit the form
        $response = $this->actingAs($staff)->post(route('project.accept_students', $project->id), [
            'students' => [$student->id],
        ]);

        // the student should be accepted
        $response->assertRedirect(route('project.show', $project->id));
        $response->assertSessionHas('success');
        $this->assertTrue($project->students()->first()->isAccepted());
    }

    /** @test */
    public function staff_cant_accept_non_first_choice_students_for_undergrad_projects()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $student->projects()->sync([$project->id => ['choice' => 2]]);
        $this->assertFalse($project->students()->first()->isAccepted());

        $response = $this->actingAs($staff)->post(route('project.accept_students', $project->id), [
            'students' => [$student->id],
        ]);

        $response->assertStatus(403);
        $response->assertSessionMissing('success');
        $this->assertFalse($project->students()->first()->isAccepted());
    }

    /** @test */
    public function staff_cant_accept_students_for_postgrad_projects()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'postgrad']);
        $student->projects()->sync([$project->id => ['choice' => 1]]);

        $response = $this->actingAs($staff)->get(route('project.show', $project->id));

        $response = $this->actingAs($staff)->post(route('project.accept_students', $project->id), [
            'students' => [$student->id],
        ]);

        $response->assertStatus(403);
        $response->assertSessionMissing('success');
        $this->assertFalse($project->students()->first()->isAccepted());
    }

    /** @test */
    public function when_staff_accept_a_student_the_students_other_project_choices_are_removed()
    {
        Mail::fake();
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project1 = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $project2 = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $student->projects()->sync([$project1->id => ['choice' => 1], $project2->id => ['choice' => 2]]);
        $this->assertEquals(2, $student->projects()->count());

        $response = $this->actingAs($staff)->post(route('project.accept_students', $project1->id), [
            'students' => [$student->id],
        ]);

        $response->assertRedirect(route('project.show', $project1->id));
        $response->assertSessionHas('success');
        $this->assertTrue($project1->students()->first()->isAccepted());
        $this->assertEquals(1, $student->projects()->count());
        $this->assertEquals($project1->id, $student->projects()->first()->id);
    }

    /** @test */
    public function when_staff_accept_a_student_the_student_gets_an_email_sent_to_them()
    {
        Mail::fake();
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $student->projects()->sync([$project->id => ['choice' => 1]]);

        $response = $this->actingAs($staff)->post(route('project.accept_students', $project->id), [
            'students' => [$student->id],
        ]);

        Mail::assertQueued(AcceptedOntoProject::class, function ($mail) use ($project, $student) {
            return $mail->hasTo($student->email);
        });
    }
}
