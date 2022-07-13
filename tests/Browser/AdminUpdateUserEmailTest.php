<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminUpdateUserEmailTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function an_admin_can_update_a_users_email_address()
    {
        $this->browse(function (Browser $browser) {
            $admin = create(User::class, ['is_admin' => true]);
            $user = create(User::class, ['is_admin' => false, 'is_staff' => true]);

            $browser->loginAs($admin)
                ->visit(route('admin.user.edit', $user->id))
                ->assertValue('@email-input', $user->email)
                ->type('email', 'not-a-valid-email')
                ->press('Update user')
                ->pause(300)
                ->assertSee('The email must be a valid email address')
                ->type('email', 'valid-email@example.com')
                ->press('Update user')
                ->pause(300)
                ->assertDontSee('The email must be a valid email address');

            $this->assertEquals('valid-email@example.com', $user->fresh()->email);
        });
    }
}
