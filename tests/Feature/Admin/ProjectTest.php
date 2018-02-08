<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Course;
use App\Project;
use App\Programme;
use Tests\TestCase;
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

    /** @test */
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
        dd($student1->projects);
        $response = $this->actingAs($admin)->get(route('admin.student.choices'));

        $response->assertSuccessful();
        $response->assertSee($student1->full_name);
        $response->assertSee($student2->full_name);
        $response->assertSee($project1->title);
        $response->assertSee($project2->title);
        $response->assertDontSee($project3->title);
    }

    /** @test */
    public function an_admin_can_accept_any_student_on_a_given_project()
    {

    }

    /** @test */
    public function an_admin_can_bulk_accept_students_onto_projects()
    {

    }

    /** @test */
    public function an_admin_can_clear_all_students_from_a_given_course()
    {

    }

    /** @test */
    public function an_admin_can_clear_all_students_postgrad_or_undergrad_students()
    {

    }

    /** @test */
    public function an_admin_can_clear_all_students()
    {

    }

    /** @test */
    public function an_admin_can_download_a_csv_of_all_project_data()
    {

    }

    /** @test */
    public function an_admin_can_impersonate_a_student()
    {

    }

    /** @test */
    public function an_admin_can_impersonate_a_member_of_staff()
    {

    }
}
