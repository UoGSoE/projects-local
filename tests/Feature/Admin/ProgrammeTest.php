<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Project;
use App\Programme;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProgrammeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function regular_users_cant_see_the_programmes_page()
    {
        $user = create(User::class);
        $programme = create(Programme::class);

        $response = $this->actingAs($user)->get(route('admin.programme.index'));

        $response->assertRedirect('/');
    }

    /** @test */
    public function admins_can_see_the_programmes_page()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        $project1->programmes()->sync([$programme1->id]);
        $project2->programmes()->sync([$programme2->id]);

        $response = $this->actingAs($admin)->get(route('admin.programme.index'));

        $response->assertSuccessful();
        $response->assertSee($programme1->title);
        $response->assertSee($programme2->title);
        $response->assertSee($project1->max_students);
        $response->assertSee($project2->max_students);
    }

    /** @test */
    public function admins_can_see_the_page_to_create_a_new_programme()
    {
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.programme.create'));

        $response->assertSuccessful();
        $response->assertSee("Create new programme");
    }

    /** @test */
    public function admins_can_create_a_new_programme()
    {
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.programme.store'), [
            'title' => "NEW PROGRAMME",
            'category' => 'undergrad',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.programme.index'));
        $response->assertSessionHas('success');
        $this->assertCount(1, Programme::all());
        $this->assertDatabaseHas('programmes', ['title' => 'NEW PROGRAMME']);
    }

    /** @test */
    public function admins_can_see_the_page_to_edit_a_new_programme()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $programme = create(Programme::class);

        $response = $this->actingAs($admin)->get(route('admin.programme.edit', $programme->id));

        $response->assertSuccessful();
        $response->assertSee("Edit programme");
    }

    /** @test */
    public function admins_can_update_an_existing_programme()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $programme = create(Programme::class);

        $response = $this->actingAs($admin)->post(route('admin.programme.update', $programme->id), [
            'title' => "UPDATED PROGRAMME",
            'category' => 'undergrad',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.programme.index'));
        $response->assertSessionHas('success');
        $this->assertCount(1, Programme::all());
        $this->assertEquals('UPDATED PROGRAMME', $programme->fresh()->title);
    }

    /** @test */
    public function updating_a_programme_but_keeping_the_same_title_doesnt_trigger_a_unique_validation_error()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $programme = create(Programme::class);

        $response = $this->actingAs($admin)->post(route('admin.programme.update', $programme->id), [
            'title' => $programme->title,
            'category' => 'undergrad',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.programme.index'));
        $response->assertSessionHas('success');
        $this->assertCount(1, Programme::all());
        $this->assertEquals($programme->title, $programme->fresh()->title);
    }


    /** @test */
    public function admins_can_delete_an_existing_programme()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);

        $response = $this->actingAs($admin)->delete(route('admin.programme.destroy', $programme1->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.programme.index'));
        $response->assertSessionHas('success');
        $this->assertCount(1, Programme::all());
        $this->assertDatabaseMissing('programmes', ['id' => $programme1->id]);
        $this->assertDatabaseHas('programmes', ['id' => $programme2->id]);
    }

    // programmes can have a category (undergrad|postgrad)
    //
}
