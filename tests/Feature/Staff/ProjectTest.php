<?php

namespace Tests\Feature\Staff;

use App\User;
use App\Course;
use App\Project;
use App\Programme;
use Tests\TestCase;
use App\Mail\AcceptedOntoProject;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function staff_can_create_a_new_undergrad_project()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);
        $course = create(Course::class);

        $response = $this->actingAs($staff)->post(route('project.store'), [
            'category' => 'undergrad',
            'title' => 'My new project',
            'pre_req' => 'Some mad skillz',
            'description' => 'Doing something',
            'max_students' => 2,
            'courses' => [$course->id],
            'programmes' => [$programme1->id, $programme2->id],
        ]);

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $project = Project::first();
        $this->assertEquals('undergrad', $project->category);
        $this->assertEquals('My new project', $project->title);
        $this->assertEquals('Some mad skillz', $project->pre_req);
        $this->assertEquals('Doing something', $project->description);
        $this->assertEquals(2, $project->max_students);
        $this->assertEquals($staff->id, $project->staff_id);
        $this->assertEquals(2, $project->programmes()->count());
        $this->assertEquals(1, $project->courses()->count());
    }

    /** @test */
    public function valid_data_is_required_to_create_a_project()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);
        $course = create(Course::class);

        $response = $this->actingAs($staff)->post(route('project.store'), [
            'category' => '',
            'title' => '',
            'pre_req' => '',
            'description' => '',
            'max_students' => 'fred',
            'courses' => [],
            'programmes' => [],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['category', 'title', 'description', 'max_students', 'courses', 'programmes']);
        $this->assertCount(0, Project::all());
    }

    /** @test */
    public function staff_can_update_their_own_projects()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $project = create(Project::class, ['staff_id' => $staff->id]);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);
        $course = create(Course::class);

        $response = $this->actingAs($staff)->post(route('project.update', $project->id), [
            'category' => 'undergrad',
            'title' => 'My new project',
            'pre_req' => 'Some mad skillz',
            'description' => 'Doing something',
            'max_students' => 2,
            'courses' => [$course->id],
            'programmes' => [$programme1->id, $programme2->id],
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('project.show', $project->id));
        $response->assertSessionHas('success');
        $project = $project->fresh();
        $this->assertEquals('undergrad', $project->category);
        $this->assertEquals('My new project', $project->title);
        $this->assertEquals('Some mad skillz', $project->pre_req);
        $this->assertEquals('Doing something', $project->description);
        $this->assertEquals(2, $project->max_students);
        $this->assertEquals($staff->id, $project->staff_id);
        $this->assertEquals(2, $project->programmes()->count());
        $this->assertEquals(1, $project->courses()->count());
    }

    /** @test */
    public function valid_data_is_required_to_update_a_project()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $project = create(Project::class, ['staff_id' => $staff->id]);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);
        $course = create(Course::class);

        $response = $this->actingAs($staff)->post(route('project.update', $project->id), [
            'category' => '',
            'title' => '',
            'pre_req' => '',
            'description' => '',
            'max_students' => 'fred',
            'courses' => [],
            'programmes' => [],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['category', 'title', 'description', 'max_students', 'courses', 'programmes']);
    }

    /** @test */
    public function staff_cant_update_other_peoples_projects()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $project = create(Project::class);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);
        $course = create(Course::class);

        $response = $this->actingAs($staff)->post(route('project.update', $project->id), [
            'category' => 'undergrad',
            'title' => 'My new project',
            'pre_req' => 'Some mad skillz',
            'description' => 'Doing something',
            'max_students' => 2,
            'courses' => [$course->id],
            'programmes' => [$programme1->id, $programme2->id],
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function staff_can_delete_their_own_projects()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $project1 = create(Project::class, ['staff_id' => $staff->id]);
        $project2 = create(Project::class);

        $response = $this->actingAs($staff)->delete(route('project.delete', $project1->id));

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('projects', ['id' => $project1->id]);
        $this->assertDatabaseHas('projects', ['id' => $project2->id]);
    }

    /** @test */
    public function staff_cant_delete_other_peoples_projects()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $project2 = create(Project::class);

        $response = $this->actingAs($staff)->delete(route('project.delete', $project2->id));

        $response->assertStatus(403);
        $response->assertSessionMissing('success');
        $this->assertDatabaseHas('projects', ['id' => $project2->id]);
    }

    /** @test */
    public function staff_can_view_their_own_project()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $project = create(Project::class, ['staff_id' => $staff->id]);

        $response = $this->actingAs($staff)->get(route('project.show', $project->id));

        $response->assertSuccessful();
        $this->assertTrue($response->data('project')->is($project));
    }

    /** @test */
    public function staff_cant_view_other_peoples_projects()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $project = create(Project::class);

        $response = $this->actingAs($staff)->get(route('project.show', $project->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function staff_can_see_which_students_have_applied_for_their_projects()
    {
        $this->withoutExceptionHandling();
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['staff_id' => $staff->id]);
        $student->projects()->sync([$project->id => ['choice' => 1]]);

        $response = $this->actingAs($staff)->get(route('project.show', $project->id));

        $response->assertSuccessful();
        $response->assertSee($student->full_name);
    }

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

        // we should see the html form markup for the student choice tickbox
        $response->assertSuccessful();
        $response->assertSee("students[{$student->id}]");

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
    public function staff_dont_get_the_option_to_accept_non_first_choice_students_for_undergrad_projects()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $student->projects()->sync([$project->id => ['choice' => 2]]);
        $this->assertFalse($project->students()->first()->isAccepted());

        $response = $this->actingAs($staff)->get(route('project.show', $project->id));

        $response->assertSuccessful();
        $response->assertDontSee("students[{$student->id}]");
    }

    /** @test */
    public function staff_dont_get_the_option_to_accept_students_for_postgrad_projects()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'postgrad']);
        $student->projects()->sync([$project->id => ['choice' => 1]]);

        $response = $this->actingAs($staff)->get(route('project.show', $project->id));

        $response->assertSuccessful();
        $response->assertDontSee("students[{$student->id}]");
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
