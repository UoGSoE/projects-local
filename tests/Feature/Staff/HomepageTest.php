<?php

namespace Tests\Feature\Staff;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Project;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function when_staff_go_to_their_homepage_they_see_a_list_of_their_current_projects()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $project1 = create(Project::class, ['staff_id' => $staff->id]);
        $project2 = create(Project::class, ['staff_id' => $staff->id]);
        $otherPersonsProject = create(Project::class);

        $response = $this->actingAs($staff)->get('/');

        $response->assertSuccessful();
        $response->assertSee($project1->title);
        $response->assertSee($project2->title);
        $response->assertDontSee($otherPersonsProject->title);
    }
}
