<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArtisanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function we_can_give_admin_rights_to_an_existing_user()
    {
        $user1 = create(User::class, ['is_admin' => false]);
        $user2 = create(User::class, ['is_admin' => false]);
        $this->assertFalse($user1->isAdmin());
        $this->assertFalse($user2->isAdmin());

        \Artisan::call('projects:makeadmin', ['username' => $user1->username]);

        $this->assertTrue($user1->fresh()->isAdmin());
        $this->assertFalse($user2->fresh()->isAdmin());
    }
}
