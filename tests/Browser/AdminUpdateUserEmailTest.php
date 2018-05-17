<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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
                ->visit(route('admin.user.show', $user->id))
                ->assertSee($user->email)
                ->pause(100)
                ->click('#change-email-button')
                ->pause(300)
                ->waitFor('#email-input')
                ->type('#email-input', 'not-a-valid-email')
                ->press('Save')
                ->pause(300)
                ->assertSee('invalid')
                ->type('#email-input', 'valid-email@example.com')
                ->press('Save')
                ->pause(300)
            ;

            $this->assertEquals('valid-email@example.com', $user->fresh()->email);
        });
    }
}
