<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_impersonate_another_user_then_become_themselves_again(): void
    {
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);

        login($admin);
        $this->assertEquals(auth()->id(), $admin->id);

        $response = $this->post(route('impersonate.start', $user->id));

        $this->assertEquals(auth()->id(), $user->id);
        $response->assertSessionHas('original_id', $admin->id);

        $response = $this->delete(route('impersonate.stop'));

        $this->assertEquals(auth()->id(), $admin->id);
        $response->assertSessionMissing('original_id');
    }
}
