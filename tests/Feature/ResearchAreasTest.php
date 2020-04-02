<?php

namespace Tests\Feature;

use App\ResearchArea;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ResearchAreasTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function regular_users_cant_see_the_research_areas_page()
    {
        $user = create(User::class);

        $response = $this->actingAs($user)->get(route('researcharea.index'));

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    /** @test */
    public function admins_can_see_the_research_areas_page()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $areas = create(ResearchArea::class, [], 3);

        $response = $this->actingAs($admin)->get(route('researcharea.index'));

        $response->assertOk();
        $this->assertEquals(3, $response->data('areas')->count());
    }

    /** @test */
    public function admins_can_add_a_new_research_area()
    {
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson(route('researcharea.store'), [
            'title' => 'SHARKS WITH LASERS',
        ]);

        $response->assertStatus(201);
        $area = ResearchArea::first();
        $this->assertEquals('SHARKS WITH LASERS', $area->title);
    }

    /** @test */
    public function admins_can_delete_an_existing_research_area()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $area1 = create(ResearchArea::class);
        $area2 = create(ResearchArea::class);

        $response = $this->actingAs($admin)->deleteJson(route('researcharea.destroy', $area1->id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('research_areas', ['id' => $area1->id]);
        $this->assertDatabaseHas('research_areas', ['id' => $area2->id]);
    }

    /** @test */
    public function admins_can_edit_an_existing_research_area()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $area1 = create(ResearchArea::class);
        $area2 = create(ResearchArea::class);

        $response = $this->actingAs($admin)->postJson(route('researcharea.update', $area1->id), [
            'title' => 'TROUT MASK REPLICA',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('TROUT MASK REPLICA', $area1->fresh()->title);
    }

    /** @test */
    public function a_title_is_required_to_create_or_update_a_research_area()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $area1 = create(ResearchArea::class, ['title' => 'HELLO']);

        $response = $this->actingAs($admin)->postJson(route('researcharea.store'), [
            'title' => '',
        ]);

        $response->assertStatus(422);
        $this->assertCount(1, ResearchArea::all());

        $response = $this->actingAs($admin)->postJson(route('researcharea.update', $area1->id), [
            'title' => '',
        ]);

        $response->assertStatus(422);
        $this->assertEquals('HELLO', $area1->fresh()->title);
    }
}
