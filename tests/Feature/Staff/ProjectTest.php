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
    public function staff_can_see_the_page_to_create_a_new_undergrad_project()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);
        $course = create(Course::class);

        $response = $this->actingAs($staff)->get(route('project.create', ['type' => 'undergrad']));

        $response->assertSuccessful();
        $response->assertSee('undergrad');
        $response->assertSee('Create new undergrad project');
    }

    /** @test */
    public function only_courses_and_programmes_of_the_correct_type_show_up_on_the_project_form()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $programme1 = create(Programme::class, ['category' => 'undergrad']);
        $programme2 = create(Programme::class, ['category' => 'postgrad']);
        $course1 = create(Course::class, ['category' => 'undergrad']);
        $course2 = create(Course::class, ['category' => 'postgrad']);

        $response = $this->actingAs($staff)->get(route('project.create', ['type' => 'undergrad']));

        $response->assertSuccessful();
        $response->assertSee('undergrad');
        $response->assertSee('Create new undergrad project');
        $response->assertSee($programme1->title);
        $response->assertDontSee($programme2->title);
        $response->assertSee($course1->title);
        $response->assertDontSee($course2->title);

        $response = $this->actingAs($staff)->get(route('project.create', ['type' => 'postgrad']));

        $response->assertSuccessful();
        $response->assertSee('postgrad');
        $response->assertSee('Create new postgrad project');
        $response->assertDontSee($programme1->title);
        $response->assertSee($programme2->title);
        $response->assertDontSee($course1->title);
        $response->assertSee($course2->title);
    }

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
            'is_confidential' => true,
            'is_placement' => true,
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
        $this->assertTrue($project->is_confidential);
        $this->assertTrue($project->is_placement);
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
    public function staff_can_see_the_page_to_edit_a_project()
    {
        $this->withoutExceptionHandling();

        $staff = create(User::class, ['is_staff' => true]);
        $project = create(Project::class, ['staff_id' => $staff->id]);

        $response = $this->actingAs($staff)->get(route('project.edit', $project->id));

        $response->assertSuccessful();
        $response->assertSee('Edit project');
    }

    /** @test */
    public function staff_can_update_their_own_projects()
    {
        $this->withoutExceptionHandling();
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
            'is_confidential' => true,
            'is_placement' => true,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('project.show', $project->id));
        $response->assertSessionHas('success');
        $project = $project->fresh();
        $this->assertEquals('undergrad', $project->category);
        $this->assertEquals('My new project', $project->title);
        $this->assertEquals('Some mad skillz', $project->pre_req);
        $this->assertEquals('Doing something', $project->description);
        $this->assertTrue($project->is_confidential);
        $this->assertTrue($project->is_placement);
        $this->assertEquals(2, $project->max_students);
        $this->assertEquals($staff->id, $project->staff_id);
        $this->assertEquals(2, $project->programmes()->count());
        $this->assertEquals(1, $project->courses()->count());
    }

    /** @test */
    public function staff_can_make_their_project_unavailable()
    {
        $this->withoutExceptionHandling();
        $staff = create(User::class, ['is_staff' => true]);
        $project = create(Project::class, ['staff_id' => $staff->id]);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);
        $course = create(Course::class);
        $this->assertFalse($project->isInactive());

        $response = $this->actingAs($staff)->post(route('project.update', $project->id), [
            'is_active' => false,
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
        $this->assertTrue($project->fresh()->isInactive());
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
    public function staff_cant_allocate_projects_to_other_users()
    {
        $this->withoutExceptionHandling();
        $staff = create(User::class, ['is_staff' => true]);
        $otherStaff = create(User::class, ['is_staff' => true]);
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
            'staff_id' => $otherStaff->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('project.show', $project->id));
        $response->assertSessionHas('success');
        $project = $project->fresh();
        $this->assertEquals($staff->id, $project->staff_id);
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

}
