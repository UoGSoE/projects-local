<?php

namespace Tests\Feature\Admin;

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
    public function an_admin_can_see_a_list_of_all_projects()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
        $project1 = create(Project::class);
        $project2 = create(Project::class);

        $response = $this->actingAs($admin)->get(route('admin.project.index'));

        $response->assertSuccessful();
        $response->assertSee($project1->title);
        $response->assertSee($project1->owner->full_name);
        $response->assertSee($project2->title);
        $response->assertSee($project2->owner->full_name);
    }

    /** @test */
    public function an_admin_can_view_a_project()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
        $project = create(Project::class);

        $response = $this->actingAs($admin)->get(route('project.show', $project->id));

        $response->assertSuccessful();
        $response->assertSee($project->title);
        $response->assertSee($project->owner->full_name);
    }

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

        // we should see the html form markup for the student choice tickbox
        $response->assertSuccessful();
        $response->assertSee("students[{$student->id}]");
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

        // we should see the html form markup for the student choice tickbox (where-as regular staff wouldn't)
        $response->assertSuccessful();
        $response->assertSee("students[{$student->id}]");

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
    public function an_admin_can_delete_a_project()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $project = create(Project::class);

        $response = $this->actingAs($admin)
                        ->from(route('home'))
                        ->delete(route('project.delete', $project->id));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    /** @test */
    public function an_admin_can_create_a_project_for_a_given_member_of_staff()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $staff1 = create(User::class, ['is_staff' => true]);
        $staff2 = create(User::class, ['is_staff' => true]);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);
        $course = create(Course::class);

        $response = $this->actingAs($admin)->post(route('project.store'), [
            'staff_id' => $staff2->id,
            'category' => 'undergrad',
            'title' => 'My new project',
            'pre_req' => 'Some mad skillz',
            'description' => 'Doing something',
            'max_students' => 2,
            'courses' => [$course->id],
            'programmes' => [$programme1->id, $programme2->id],
        ]);

        $response->assertStatus(302);
        $project = Project::first();
        $response->assertRedirect(route('project.show', $project->id));
        $this->assertEquals('undergrad', $project->category);
        $this->assertEquals('My new project', $project->title);
        $this->assertEquals('Some mad skillz', $project->pre_req);
        $this->assertEquals('Doing something', $project->description);
        $this->assertEquals(2, $project->max_students);
        $this->assertEquals($staff2->id, $project->staff_id);
        $this->assertEquals(2, $project->programmes()->count());
        $this->assertEquals(1, $project->courses()->count());
    }

    /** @test */
    public function an_admin_can_update_a_project()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $staff1 = create(User::class, ['is_staff' => true]);
        $staff2 = create(User::class, ['is_staff' => true]);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);
        $course = create(Course::class);
        $project = create(Project::class, ['staff_id' => $staff1->id]);

        $response = $this->actingAs($admin)->post(route('project.update', $project->id), [
            'staff_id' => $staff2->id,
            'category' => 'undergrad',
            'title' => 'My new project',
            'pre_req' => 'Some mad skillz',
            'description' => 'Doing something',
            'max_students' => 2,
            'courses' => [$course->id],
            'programmes' => [$programme1->id, $programme2->id],
        ]);

        $response->assertStatus(302);
        $project = Project::first();
        $response->assertRedirect(route('project.show', $project->id));
        $this->assertEquals('undergrad', $project->category);
        $this->assertEquals('My new project', $project->title);
        $this->assertEquals('Some mad skillz', $project->pre_req);
        $this->assertEquals('Doing something', $project->description);
        $this->assertEquals(2, $project->max_students);
        $this->assertEquals($staff2->id, $project->staff_id);
        $this->assertEquals(2, $project->programmes()->count());
        $this->assertEquals(1, $project->courses()->count());
    }

    /** test */
    public function an_admin_can_see_all_student_project_choices()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        $project3 = create(Project::class);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $student1->projects()->sync([
            $project1->id => ['choice' => 1],
            $project2->id => ['choice' => 2],
        ]);
        $student2->projects()->sync([
            $project1->id => ['choice' => 3],
            $project2->id => ['choice' => 1],
        ]);
        //dd($student1->projects);
        $response = $this->actingAs($admin)->get(route('admin.student.choices'));

        $response->assertSuccessful();
        $response->assertSee($student1->full_name);
        $response->assertSee($student2->full_name);
        $response->assertSee($project1->title);
        $response->assertSee($project2->title);
        $response->assertDontSee($project3->title);
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

    /** @test */
    public function an_admin_can_clear_all_students_from_a_given_course()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $students = create(User::class, ['is_staff' => false], 3);
        $course->students()->sync($students->pluck('id')->toArray());

        $response = $this->actingAs($admin)->delete(route('course.remove_students', $course->id));

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertCount(0, $course->students);
    }

    /** @test */
    public function an_admin_can_clear_all_postgrad_or_undergrad_students()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $undergrad = create(User::class, ['is_staff' => false]);
        $postgrad = create(User::class, ['is_staff' => false]);
        $ugradProject = create(Project::class, ['category' => 'undergrad']);
        $pgradProject = create(Project::class, ['category' => 'postgrad']);
        $undergrad->projects()->sync([$ugradProject->id => ['choice' => 1]]);
        $postgrad->projects()->sync([$pgradProject->id => ['choice' => 1]]);

        $response = $this->actingAs($admin)->delete(route('students.remove_undergrads'));

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertDatabaseMissing('users', ['id' => $undergrad->id]);
        $this->assertDatabaseHas('users', ['id' => $postgrad->id]);

        $response = $this->actingAs($admin)->delete(route('students.remove_postgrads'));

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertDatabaseMissing('users', ['id' => $postgrad->id]);
    }

    /** @test */
    public function an_admin_can_clear_all_students()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $staff = create(User::class, ['is_staff' => true]);

        $response = $this->actingAs($admin)->delete(route('students.remove_all'));

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertDatabaseMissing('users', ['id' => $student1->id]);
        $this->assertDatabaseMissing('users', ['id' => $student2->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseHas('users', ['id' => $staff->id]);
    }

    /** @test */
    public function an_admin_can_download_a_spreadsheet_of_all_project_data()
    {
	$this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $project1->students()->sync([$student1->id => ['choice' => 1]]);
        $project2->students()->sync([$student2->id => ['choice' => 2]]);

        $response = $this->actingAs($admin)->get(route('export.projects.excel'));

        $response->assertSuccessful();
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertEquals('attachment; filename="uog_project_data.xlsx"', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function an_admin_can_impersonate_another_user_then_become_themselves_again()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);

        login($admin);
        $this->assertEquals(auth()->id(), $admin->id);

        $response = $this->post(route('impersonate.start', $user->id));

        $this->assertEquals(auth()->id(), $user->id);
        $response->assertSessionHas('original_id', $admin->id);

        $response = $this->delete(route('impersonate.stop'));

        $this->assertEquals(auth()->id(), $admin->id);
        $response->assertSessionMissing('original_id');
    }
}
