<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Project;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportTest extends TestCase
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
}
