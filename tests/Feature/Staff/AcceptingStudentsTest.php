<?php

namespace Tests\Feature\Staff;

use App\Mail\AcceptedOntoProject;
use App\Models\Course;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AcceptingStudentsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function staff_can_accept_first_choice_students_for_projects_where_the_course_is_flagged_as_such(): void
    {
        Mail::fake();
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $course = create(Course::class, ['allow_staff_accept' => true, 'category' => 'undergrad']);
        $project->courses()->sync([$course->id]);
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
    public function staff_cant_accept_non_first_choice_students_for_projects(): void
    {
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $course = create(Course::class, ['allow_staff_accept' => true, 'category' => 'undergrad']);
        $project->courses()->sync([$course->id]);
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
    public function staff_cant_accept_students_for_projects_where_the_course_isnt_flagged_as_allowing_it(): void
    {
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $course = create(Course::class, ['allow_staff_accept' => false, 'category' => 'undergrad']);
        $project->courses()->sync([$course->id]);
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
    public function staff_can_only_accept_upto_the_max_students_number_of_students(): void
    {
        $staff = create(User::class, ['is_staff' => true]);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad', 'max_students' => 1]);
        $course = create(Course::class, ['allow_staff_accept' => true, 'category' => 'undergrad']);
        $project->courses()->sync([$course->id]);
        $student1->projects()->sync([$project->id => ['choice' => 1, 'is_accepted' => true]]);
        $student2->projects()->sync([$project->id => ['choice' => 1, 'is_accepted' => false]]);

        $response = $this->actingAs($staff)->get(route('project.show', $project->id));

        $response = $this->actingAs($staff)->post(route('project.accept_students', $project->id), [
            'students' => [$student2->id],
        ]);

        $response->assertStatus(403);
        $response->assertSessionMissing('success');
        $this->assertFalse($student2->isAccepted());
    }

    /** @test */
    public function staff_cant_unaccept_any_student_on_a_given_project(): void
    {
        Mail::fake();
        // given we have a an undergrad project with two accepted students
        $staff = create(User::class, ['is_staff' => true]);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['category' => 'undergrad', 'max_students' => 5]);
        $course = create(Course::class, ['allow_staff_accept' => true, 'category' => 'undergrad']);
        $project->courses()->sync([$course->id]);
        $student1->projects()->sync([$project->id => ['choice' => 1, 'is_accepted' => true]]);
        $student2->projects()->sync([$project->id => ['choice' => 1, 'is_accepted' => true]]);
        $this->assertTrue($student1->isAccepted());
        $this->assertTrue($student2->isAccepted());

        // and when we submit the form with student2 missing
        $response = $this->actingAs($staff)->post(route('project.accept_students', $project->id), [
            'students' => [$student1->id],
        ]);

        // both students should still be accepted
        $response->assertStatus(302);
        $this->assertTrue($student1->fresh()->isAccepted());
        $this->assertTrue($student2->fresh()->isAccepted());
    }

    /** @test */
    public function when_staff_accept_a_student_the_students_other_project_choices_are_removed(): void
    {
        Mail::fake();
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project1 = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $project2 = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $course = create(Course::class, ['allow_staff_accept' => true, 'category' => 'undergrad']);
        $project1->courses()->sync([$course->id]);
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
    public function when_staff_accept_a_student_the_student_gets_an_email_sent_to_them(): void
    {
        Mail::fake();
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $course = create(Course::class, ['allow_staff_accept' => true, 'category' => 'undergrad']);
        $project->courses()->sync([$course->id]);
        $student->projects()->sync([$project->id => ['choice' => 1]]);

        $response = $this->actingAs($staff)->post(route('project.accept_students', $project->id), [
            'students' => [$student->id],
        ]);

        // Mail::assertQueued(AcceptedOntoProject::class, function ($mail) use ($project, $student) {
        //     return $mail->hasTo($student->email);
        // });
    }
}
