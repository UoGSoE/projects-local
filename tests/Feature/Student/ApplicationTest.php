<?php

namespace Tests\Feature\Student;

use App\Mail\ChoiceConfirmation;
use App\Models\Course;
use App\Models\Project;
use App\Models\ResearchArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_student_only_sees_projects_for_the_course_they_are_on_when_visiting_the_homepage(): void
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
        $this->assertCount(1, $response->data('projects'));
        $this->assertTrue($response->data('projects')[0]['id'] == $project1->id);
    }

    /** @test */
    public function projects_which_are_marked_as_inactive_do_not_show_up_on_the_list(): void
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
        $this->assertCount(1, $response->data('projects'));
        $this->assertTrue($response->data('projects')[0]['id'] == $activeProject->id);
    }

    /** @test */
    public function projects_which_are_fully_allocated_do_not_show_up_on_the_list(): void
    {
        // given we have students and a course
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $student3 = create(User::class, ['is_staff' => false]);
        $course1 = create(Course::class);
        // and the students are on that course
        $course1->students()->save($student1);
        $course1->students()->save($student2);
        $course1->students()->save($student3);
        // and a fully allocated project for that course
        $allocatedProject = create(Project::class, ['max_students' => 1]);
        $allocatedProject->courses()->sync([$course1->id]);
        $student1->projects()->sync([$allocatedProject->id => ['choice' => 1]]);
        $allocatedProject->accept($student1); // leaving no places free
        // and places left on another project
        $unallocatedProject = create(Project::class, ['max_students' => 2]);
        $unallocatedProject->courses()->sync([$course1->id]);
        $student2->projects()->sync([$allocatedProject->id => ['choice' => 1]]);
        $allocatedProject->accept($student2); // leaving one place still free

        // when the student goes to the homepage
        $response = $this->actingAs($student3)->get(route('home'));

        // they should only see the allocated project
        $response->assertSuccessful();
        // dd($response->data('projects'));
        $this->assertCount(1, $response->data('projects'));
        $this->assertTrue($response->data('projects')[1]['id'] == $unallocatedProject->id);

        // $response->data('projects')->assertNotContains($allocatedProject);
        // $response->data('projects')->assertContains($unallocatedProject);
    }

    /** @test */
    public function research_areas_are_passed_to_the_choices_page(): void
    {
        // given we have students and a course
        $student1 = create(User::class, ['is_staff' => false]);
        $course1 = create(Course::class);
        // and the students are on that course
        $course1->students()->save($student1);
        $area1 = create(ResearchArea::class);
        $area2 = create(ResearchArea::class);

        // when the student goes to the homepage
        $response = $this->actingAs($student1)->get(route('home'));

        // they should only see the allocated project
        $response->assertSuccessful();
        $this->assertTrue($response->data('researchAreas')->contains($area1));
        $this->assertTrue($response->data('researchAreas')->contains($area2));
    }

    /** @test */
    public function a_student_cant_apply_for_a_more_than_the_required_number_of_projects(): void
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
            ],
        ]);

        // then they get an error and no project choices are stored
        $response->assertStatus(302);
        $response->assertSessionHas('errors');
        $this->assertCount(0, $student->projects);
    }

    /** @test */
    public function a_student_can_apply_for_the_required_number_of_projects(): void
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // given we have a student on a course
        $student = create(User::class, ['is_staff' => false]);
        $course = create(Course::class);
        $course->students()->save($student);

        $area1 = create(ResearchArea::class);
        $area2 = create(ResearchArea::class);

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
            ],
            'research_area' => $area2->title,
        ]);

        // then they get the thank you page and the choices are stored
        $response->assertStatus(302);
        $response->assertRedirect(route('thank_you'));
        $response->assertSessionMissing('errors');
        $this->assertCount(2, $student->projects);
        $this->assertEquals($area2->title, $student->fresh()->research_area);

        // And a mail is sent (queued) to them with confirmation
        Mail::assertQueued(ChoiceConfirmation::class, function ($mail) use ($student) {
            return $mail->hasTo($student->email);
        });
    }

    /** @test */
    public function students_cant_apply_for_more_than_three_projects_from_the_same_supervisor(): void
    {
        Mail::fake();
        // given we have a student on a course
        $student = create(User::class, ['is_staff' => false]);
        $supervisor = create(User::class, ['is_staff' => true]);
        $course = create(Course::class);
        $course->students()->save($student);

        $area1 = create(ResearchArea::class);
        $area2 = create(ResearchArea::class);

        $project1 = create(Project::class, ['staff_id' => $supervisor->id]);
        $project2 = create(Project::class, ['staff_id' => $supervisor->id]);
        $project3 = create(Project::class, ['staff_id' => $supervisor->id]);
        $project4 = create(Project::class, ['staff_id' => $supervisor->id]);
        $project5 = create(Project::class);
        $course->projects()->sync([$project1->id, $project2->id, $project3->id, $project4->id, $project5->id]);
        // and given that the required number to apply for is 2
        config(['projects.required_choices' => 5]);

        // then if they apply for 2
        $response = $this->actingAs($student)->postJson(route('projects.choose'), [
            'choices' => [
                1 => $project3->id,
                2 => $project1->id,
                3 => $project2->id,
                4 => $project5->id,
                5 => $project4->id,
            ],
            'research_area' => $area2->title,
        ]);

        // then they get told they have chosen too many for $supervisor
        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'You cannot choose more than three projects with the same supervisor',
            'errors' => [
                'supervisor' => [
                    'You cannot choose more than three projects with the same supervisor',
                ],
            ],
        ]);
        $this->assertCount(0, $student->projects);
    }

    /** @test */
    public function postgrad_students_must_give_a_research_area_alongside_their_choices(): void
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // given we have a student on a course
        $student = create(User::class, ['is_staff' => false]);
        $course = create(Course::class, ['category' => 'postgrad']);
        $course->students()->save($student);

        $area1 = create(ResearchArea::class);
        $area2 = create(ResearchArea::class);

        // and given we have three projects
        $project1 = create(Project::class, ['category' => 'undergrad']);
        $project2 = create(Project::class, ['category' => 'undergrad']);
        $project3 = create(Project::class, ['category' => 'undergrad']);
        $course->projects()->sync([$project1->id, $project2->id, $project3->id]);
        // and given that the required number to apply for is 2
        config(['projects.required_choices' => 2]);

        // then if they apply for 2
        $response = $this->actingAs($student)->post(route('projects.choose'), [
            'choices' => [
                1 => $project3->id,
                2 => $project1->id,
            ],
            'research_area' => $area2->title,
        ]);

        // then they get the thank you page and the choices are stored
        $response->assertStatus(302);
        $response->assertRedirect(route('thank_you'));
        $response->assertSessionMissing('errors');
        $this->assertCount(2, $student->projects);
        $this->assertEquals($area2->title, $student->fresh()->research_area);

        // And a mail is sent (queued) to them with confirmation
        Mail::assertQueued(ChoiceConfirmation::class, function ($mail) use ($student) {
            return $mail->hasTo($student->email);
        });
    }

    /** @test */
    public function a_student_cant_apply_for_a_less_than_the_required_number_of_projects(): void
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
            ],
        ]);

        // then they get an error and no project choices are stored
        $response->assertStatus(302);
        $response->assertSessionHas('errors');
        $this->assertCount(0, $student->projects);
    }

    /** @test */
    public function a_student_gets_a_confirmation_email_with_the_projects_they_have_chosen_when_then_apply(): void
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        // given we have a student on a course
        $student = create(User::class, ['is_staff' => false]);
        $course = create(Course::class);
        $course->students()->save($student);
        $area = create(ResearchArea::class);
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
            ],
            'research_area' => $area->title,
        ]);

        // then they get the thank you page and the choices are stored
        $response->assertStatus(302);
        $response->assertRedirect(route('thank_you'));
        $response->assertSessionMissing('errors');
        $this->assertCount(2, $student->projects);

        // and they are sent a confirmation email
        Mail::assertQueued(ChoiceConfirmation::class, function ($mail) use ($student) {
            return $mail->hasTo($student->email) && $mail->student->is($student);
        });
    }

    /** @test */
    public function students_cant_submit_new_choices_if_they_have_already_been_accepted(): void
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
        // and given the student has been accepted onto a project
        $student->projects()->sync([$project1->id => ['choice' => 1]]);
        $project1->accept($student);
        $this->assertEquals(1, $student->fresh()->projects->count());
        // and given that the required number to apply for is 2
        config(['projects.required_choices' => 2]);

        // if they visit their homepage
        $response = $this->actingAs($student)->get('/');

        // they see the warning
        $response->assertSuccessful();
        $response->assertSee('You cannot choose new projects');

        // then if somehow they apply for 2 despite the form not being there
        $response = $this->actingAs($student)->post(route('projects.choose'), [
            'choices' => [
                1 => $project3->id,
                2 => $project1->id,
            ],
        ]);

        // then they get redirected and no new choices are saved
        $response->assertRedirect('/');
        $this->assertEquals(1, $student->fresh()->projects->count());
    }

    /** @test */
    public function students_cant_submit_choices_if_the_course_deadline_has_passed(): void
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // given we have a student on a course
        $student = create(User::class, ['is_staff' => false]);
        $course = create(Course::class, ['application_deadline' => now()->subDays(1), 'category' => 'undergrad']);
        $course->students()->save($student);
        // and given we have three projects
        $project1 = create(Project::class, ['category' => 'undergrad']);
        $project2 = create(Project::class, ['category' => 'undergrad']);
        $project3 = create(Project::class, ['category' => 'undergrad']);
        $course->projects()->sync([$project1->id, $project2->id, $project3->id]);

        // if they visit their homepage
        $response = $this->actingAs($student)->get('/');

        // they see the deadline warning but can still see the projects
        $response->assertSuccessful();
        $response->assertSee('deadline has passed');
        $response->assertSee($project1->title);
    }

    /** @test */
    public function students_cant_submit_choices_if_they_dont_have_an_email_address(): void
    {
        Mail::fake();
        $this->withoutExceptionHandling();
        // given we have a student on a course
        $student = create(User::class, ['is_staff' => false, 'email' => '']);
        $course = create(Course::class);
        $course->students()->save($student);
        // and given we have three projects
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        $project3 = create(Project::class);
        $course->projects()->sync([$project1->id, $project2->id, $project3->id]);

        // if they visit their homepage
        $response = $this->actingAs($student)->get('/');

        // they see the warning
        $response->assertSuccessful();
        $response->assertSee('email address');
        $response->assertDontSee($project1->title);
    }
}
