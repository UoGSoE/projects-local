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
    public function an_admin_can_see_the_bulk_edit_project_options_page()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $ugProject1 = create(Project::class, ['category' => 'undergrad']);
        $ugProject2 = create(Project::class, ['category' => 'undergrad']);
        $pgProject = create(Project::class, ['category' => 'postgrad']);

        $response = $this->actingAs($admin)->get(route('admin.project.bulk-options', 'undergrad'));

        $response->assertSuccessful();
        $response->assertSee($ugProject1->title);
        $response->assertSee($ugProject2->title);
        $response->assertDontSee($pgProject->title);
    }

    /** @test */
    public function an_admin_can_bulk_update_project_options()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $ugProject1 = create(Project::class, ['category' => 'undergrad', 'is_active' => true]);
        $ugProject2 = create(Project::class, ['category' => 'undergrad', 'is_active' => false]);
        $ugProject3 = create(Project::class, ['category' => 'undergrad', 'is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.project.bulk-options.update', ['category' => 'undergrad']), [
            'active' => [
                $ugProject1->id => 0,
                $ugProject2->id => 1,
            ],
            'delete' => [
                $ugProject3->id => 1,
            ]
        ]);

        $response->assertRedirect(route('admin.project.index', ['category' => 'undergrad']));
        $this->assertFalse($ugProject1->fresh()->isActive());
        $this->assertTrue($ugProject2->fresh()->isActive());
        $this->assertDatabaseMissing('projects', ['id' => $ugProject3->id]);
    }
}
