<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowerCaseUserRecordsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function we_can_convert_all_usernames_and_emails_to_lowercase()
    {
        $user1 = create(User::class, ['username' => 'FRED', 'email' => 'FRED@example.com']);
        $user2 = create(User::class, ['username' => 'JimMY', 'email' => 'JiMMy@EXample.com']);

        $this->artisan('lowercase:users');

        $this->assertEquals('fred', $user1->fresh()->username);
        $this->assertEquals('fred@example.com', $user1->fresh()->email);
        $this->assertEquals('jimmy', $user2->fresh()->username);
        $this->assertEquals('jimmy@example.com', $user2->fresh()->email);
    }
}
