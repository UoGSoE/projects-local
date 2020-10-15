<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_see_a_list_of_all_undergrad_projects()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
        $project1 = create(Project::class, ['category' => 'undergrad']);
        $project2 = create(Project::class, ['category' => 'undergrad']);
        $project3 = create(Project::class, ['category' => 'postgrad']);

        $response = $this->actingAs($admin)->get(route('admin.project.index', 'undergrad'));

        $response->assertSuccessful();
        $response->assertSee($project1->title);
        $response->assertSee($project1->owner->full_name);
        $response->assertSee($project2->title);
        $response->assertSee($project2->owner->full_name);
        $response->assertDontSee($project3->title);
        $response->assertDontSee($project3->owner->full_name);
    }

    /** @test */
    public function an_admin_can_see_a_list_of_all_postgrad_projects()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
        $project1 = create(Project::class, ['category' => 'postgrad']);
        $project2 = create(Project::class, ['category' => 'undergrad']);
        $project3 = create(Project::class, ['category' => 'postgrad']);

        $response = $this->actingAs($admin)->get(route('admin.project.index', 'postgrad'));

        $response->assertSuccessful();
        $response->assertSee($project1->title);
        $response->assertSee($project1->owner->full_name);
        $response->assertDontSee($project2->title);
        $response->assertDontSee($project2->owner->full_name);
        $response->assertSee($project3->title);
        $response->assertSee($project3->owner->full_name);
    }
}
