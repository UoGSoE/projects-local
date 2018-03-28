<?php

namespace Tests\Feature\Student;

use App\User;
use App\Course;
use App\Project;
use Tests\TestCase;
use App\Mail\ChoiceConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_student_only_sees_projects_for_the_course_they_are_on_when_visiting_the_homepage()
    {
        // given we have a student
        $student = create(User::class, ['is_staff' => false]);
        // and two courses
        $course1 = create(Course::class);
        $course2 = create(Course::class);
        // and we have a project assigned to course1
        $project1 = create(Project::class);
        $project1->courses()->sync([$course1->id]);
        // and another project assigned to course2
        $project2 = create(Project::class);
        $project2->courses()->sync([$course2->id]);
        // but the student is on course1
        $course1->students()->save($student);

        // when the student goes to the homepage
        $response = $this->actingAs($student)->get(route('home'));

        // they should only see the projects for course1
        $response->assertSuccessful();
        $response->data('projects')->assertContains($project1);
        $response->data('projects')->assertNotContains($project2);
    }

    /** @test */
    public function projects_which_are_marked_as_inactive_do_not_show_up_on_the_list()
    {
        // given we have a student and a course
        $student = create(User::class, ['is_staff' => false]);
        $course1 = create(Course::class);
        // and an active project for that course
        $activeProject = create(Project::class);
        $activeProject->courses()->sync([$course1->id]);
        // and an inactive one
        $inactiveProject = create(Project::class, ['is_active' => false]);
        $inactiveProject->courses()->sync([$course1->id]);
        // and the student is on that course
        $course1->students()->save($student);

        // when the student goes to the homepage
        $response = $this->actingAs($student)->get(route('home'));

        // they should only see the active project
        $response->assertSuccessful();
        $response->data('projects')->assertContains($activeProject);
        $response->data('projects')->assertNotContains($inactiveProject);
    }

    /** @test */
    public function a_student_cant_apply_for_a_more_than_the_required_number_of_projects()
    {
        // given we have a student on a course
        $student = create(User::class, ['is_staff' => false]);
        $course = create(Course::class);
        $course->students()->save($student);
        // and given we have three projects
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        $project3 = create(Project::class);
        $course->projects()->sync([$project1->id, $project2->id, $project3->id]);
        // and given that the required number of project choices is 2
        config(['projects.required_choices' => 2]);

        // then if they apply for 3
        $response = $this->actingAs($student)->post(route('projects.choose'), [
            'choices' => [
                [1 => $project3->id],
                [2 => $project1->id],
                [3 => $project2->id],
            ]
        ]);

        // then they get an error and no project choices are stored
        $response->assertStatus(302);
        $response->assertSessionHas('errors');
        $this->assertCount(0, $student->projects);
    }

    /** @test */
    public function a_student_can_apply_for_the_required_number_of_projects()
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // given we have a student on a course
        $student = create(User::class, ['is_staff' => false]);
        $course = create(Course::class);
        $course->students()->save($student);
        // and given we have three projects
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        $project3 = create(Project::class);
        $course->projects()->sync([$project1->id, $project2->id, $project3->id]);
        // and given that the required number to apply for is 2
        config(['projects.required_choices' => 2]);

        // then if they apply for 2
        $response = $this->actingAs($student)->post(route('projects.choose'), [
            'choices' => [
                1 => $project3->id,
                2 => $project1->id,
            ]
        ]);

        // then they get the thank you page and the choices are stored
        $response->assertStatus(302);
        $response->assertRedirect(route('thank_you'));
        $response->assertSessionMissing('errors');
        $this->assertCount(2, $student->projects);
    }

    /** @test */
    public function a_student_cant_apply_for_a_less_than_the_required_number_of_projects()
    {
        // given we have a student on a course
        $student = create(User::class, ['is_staff' => false]);
        $course = create(Course::class);
        $course->students()->save($student);
        // and given we have three projects
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        $project3 = create(Project::class);
        $course->projects()->sync([$project1->id, $project2->id, $project3->id]);
        // and given that the required number of project choices is 3
        config(['projects.required_choices' => 3]);

        // then if they apply for 2
        $response = $this->actingAs($student)->post(route('projects.choose'), [
            'choices' => [
                1 => $project3->id,
                2 => $project1->id,
            ]
        ]);

        // then they get an error and no project choices are stored
        $response->assertStatus(302);
        $response->assertSessionHas('errors');
        $this->assertCount(0, $student->projects);
    }

    /** @test */
    public function a_student_gets_a_confirmation_email_with_the_projects_they_have_chosen_when_then_apply()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        // given we have a student on a course
        $student = create(User::class, ['is_staff' => false]);
        $course = create(Course::class);
        $course->students()->save($student);
        // and given we have three projects
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        $project3 = create(Project::class);
        $course->projects()->sync([$project1->id, $project2->id, $project3->id]);
        // and given that the maximum they can apply for is 2
        config(['projects.required_choices' => 2]);

        // then if they apply for 2
        $response = $this->actingAs($student)->post(route('projects.choose'), [
            'choices' => [
                1 => $project3->id,
                2 => $project1->id,
            ]
        ]);

        // then they get the thank you page and the choices are stored
        $response->assertStatus(302);
        $response->assertRedirect(route('thank_you'));
        $response->assertSessionMissing('errors');
        $this->assertCount(2, $student->projects);

        // and they are sent a confirmation email
        Mail::assertQueued(ChoiceConfirmation::class, function ($mail) use ($student, $project1, $project2, $project3) {
            return $mail->hasTo($student->email) && $mail->student->is($student);
        });
    }
}
