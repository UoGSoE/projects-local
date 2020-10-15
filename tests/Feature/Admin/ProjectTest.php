<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Mail\AcceptedOntoProject;
use App\Models\Programme;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

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
            'type' => 'B.Eng',
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
    public function an_admin_can_edit_a_project_even_if_editing_is_disabled()
    {
        // see 'an_admin_can_stop_academics_from_editing_projects' test and
        // Feature\Staff\ProjectTest@staff_cant_update_their_own_projects_if_the_admins_have_disabled_editing
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $staff1 = create(User::class, ['is_staff' => true]);
        $staff2 = create(User::class, ['is_staff' => true]);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);
        $course = create(Course::class);
        $project = create(Project::class, ['staff_id' => $staff1->id, 'category' => 'undergrad']);

        option(['undergrad_editing_disabled' => now()->format('Y-m-d H:i')]);

        $response = $this->actingAs($admin)->get(route('project.edit', $project->id));

        $response->assertOk();
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
                [
                    'id' => $ugProject1->id,
                    'is_active' => 0,
                ],
                [
                    'id' => $ugProject2->id,
                    'is_active' => 1,
                ],
            ],
            'delete' => [
                $ugProject3->id,
            ],
        ]);

        $response->assertRedirect(route('admin.project.index', ['category' => 'undergrad']));
        $this->assertFalse($ugProject1->fresh()->isActive());
        $this->assertTrue($ugProject2->fresh()->isActive());
        $this->assertDatabaseMissing('projects', ['id' => $ugProject3->id]);
    }

    /** @test */
    public function an_admin_can_stop_academics_from_editing_projects()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $ugProject1 = create(Project::class, ['category' => 'undergrad', 'is_active' => true]);
        $ugProject2 = create(Project::class, ['category' => 'undergrad', 'is_active' => false]);
        $ugProject3 = create(Project::class, ['category' => 'undergrad', 'is_active' => true]);

        $this->assertNull(option('undergrad_editing_disabled'));

        $response = $this->actingAs($admin)->post(route('admin.project.toggle_editing'), [
            'category' => 'undergrad',
        ]);

        $response->assertRedirect(route('admin.project.index', ['category' => 'undergrad']));
        $this->assertNotNull(option('undergrad_editing_disabled'));

        $response = $this->actingAs($admin)->post(route('admin.project.toggle_editing'), [
            'category' => 'undergrad',
        ]);

        $response->assertRedirect(route('admin.project.index', ['category' => 'undergrad']));
        $this->assertNull(option('undergrad_editing_disabled'));

        $this->assertNull(option('postgrad_editing_disabled'));

        $response = $this->actingAs($admin)->post(route('admin.project.toggle_editing'), [
            'category' => 'postgrad',
        ]);

        $response->assertRedirect(route('admin.project.index', ['category' => 'postgrad']));
        $this->assertNotNull(option('postgrad_editing_disabled'));

        $response = $this->actingAs($admin)->post(route('admin.project.toggle_editing'), [
            'category' => 'postgrad',
        ]);

        $response->assertRedirect(route('admin.project.index', ['category' => 'postgrad']));
        $this->assertNull(option('postgrad_editing_disabled'));
    }
}
