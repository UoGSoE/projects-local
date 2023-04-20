<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCopyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function staff_can_see_a_button_for_making_the_correct_kind_of_copy(): void
    {
        $this->withoutExceptionHandling();
        $staff = create(User::class, ['is_staff' => true]);
        $ugradProject = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $pgradProject = create(Project::class, ['staff_id' => $staff->id, 'category' => 'postgrad']);

        $response = $this->actingAs($staff)->get(route('project.show', $ugradProject->id));
        $response->assertSuccessful();
        $response->assertSee('Make a postgrad copy');

        $response = $this->actingAs($staff)->get(route('project.show', $pgradProject->id));
        $response->assertSuccessful();
        $response->assertSee('Make an undergrad copy');
    }

    /** @test */
    public function staff_can_make_a_postgrad_copy_of_an_undergrad_project(): void
    {
        $this->withoutExceptionHandling();
        $staff = create(User::class, ['is_staff' => true]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);

        $response = $this->actingAs($staff)->get(route('project.copy', $project->id));

        $response->assertSuccessful();
        $response->assertSee($project->title);
        $response->assertSee('Create new postgrad project');
    }

    /** @test */
    public function staff_can_make_an_undergrad_copy_of_a_postgrad_project(): void
    {
        $this->withoutExceptionHandling();
        $staff = create(User::class, ['is_staff' => true]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'postgrad']);

        $response = $this->actingAs($staff)->get(route('project.copy', $project->id));

        $response->assertSuccessful();
        $response->assertSee($project->title);
        $response->assertSee('Create new undergrad project');
    }
}
