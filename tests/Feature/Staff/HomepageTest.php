<?php

namespace Tests\Feature\Staff;

use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function when_staff_go_to_their_homepage_they_see_a_list_of_their_current_projects()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $project1 = create(Project::class, ['staff_id' => $staff->id]);
        $project2 = create(Project::class, ['staff_id' => $staff->id, 'is_active' => false]);
        $otherPersonsProject = create(Project::class);

        $response = $this->actingAs($staff)->get('/');

        $response->assertSuccessful();
        $response->assertSee($project1->title);
        $response->assertSeeTextInOrder(['Inactive', $project2->title]);
        $response->assertDontSee($otherPersonsProject->title);
    }
}
