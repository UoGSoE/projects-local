<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminToggleTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function an_admin_can_give_and_remove_admin_rights_to_other_users()
    {
        $this->browse(function (Browser $browser) {
            $admin = create(User::class, ['is_admin' => true]);
            $user = create(User::class, ['is_admin' => false, 'is_staff' => true]);
            $this->assertFalse($user->fresh()->isAdmin());

            $browser->loginAs($admin)
                    ->visit(route('admin.users', 'staff'))
                    ->assertSee($user->username)
                    ->clickLink($user->username)
                    ->assertSee('Give Admin Rights')
                    ->click('#admintoggle-'.$user->id)
                    ->waitForReload()
                    ->assertSee('Remove Admin Rights')
                    ->click('#admintoggle-'.$user->id)
                    ->waitForReload()
                    ->assertSee('Give Admin Rights')
                    ->click('#admintoggle-'.$user->id)
                    ->waitForReload();
            $this->assertTrue($user->fresh()->isAdmin());
        });
    }

    /** @test */
    public function an_admin_does_not_get_the_option_to_disable_their_own_admin_status()
    {
        $this->browse(function (Browser $browser) {
            $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
            $this->assertTrue($admin->fresh()->isAdmin());

            $browser->loginAs($admin)
                    ->visit(route('admin.user.show', $admin->id))
                    ->assertSee($admin->username)
                    ->assertDontSee('Remove Admin Rights');
        });
    }
}
