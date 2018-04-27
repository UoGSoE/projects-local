<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function regular_users_cant_see_the_user_admin_pages()
    {
        $user = create(User::class);

        $response = $this->actingAs($user)->get(route('admin.users', 'staff'));

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    /** @test */
    public function admins_can_see_a_list_of_all_staff()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $user1 = create(User::class, ['is_staff' => true]);
        $user2 = create(User::class, ['is_staff' => true]);

        $response = $this->actingAs($admin)->get(route('admin.users', 'staff'));

        $response->assertSuccessful();
        $response->assertSee($user1->surname);
        $response->assertSee($user2->surname);
    }

    /** @test */
    public function admins_can_give_and_take_away_admin_rights_to_other_users()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);
        $this->assertFalse($user->fresh()->isAdmin());

        $response = $this->actingAs($admin)->post(route('admin.users.toggle_admin', $user->id));

        $response->assertSuccessful();
        $response->assertJson(['status' => 'ok']);
        $this->assertTrue($user->fresh()->isAdmin());

        $response = $this->actingAs($admin)->post(route('admin.users.toggle_admin', $user->id));

        $response->assertSuccessful();
        $response->assertJson(['status' => 'ok']);
        $this->assertFalse($user->fresh()->isAdmin());
    }

    /** @test */
    public function admins_cant_take_away_admin_rights_from_themselves()
    {
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.users.toggle_admin', $admin->id));

        $response->assertStatus(422);
        $this->assertTrue($admin->fresh()->isAdmin());
    }

    /** @test */
    public function admins_can_view_an_individual_user()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);

        $response = $this->actingAs($admin)->get(route('admin.user.show', $user->id));

        $response->assertSuccessful();
        $response->assertSee($user->full_name);
    }

    /** @test */
    public function admins_can_edit_a_users_email_address()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);

        $response = $this->actingAs($admin)->postJson(route('api.user.update', $user->id), [
            'email' => 'fred@example.com',
        ]);

        $response->assertSuccessful();
        $this->assertEquals('fred@example.com', $user->fresh()->email);
    }
}
