<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminToggleTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function an_admin_can_give_and_remove_admin_rights_to_other_users()
    {
        $this->browse(function (Browser $browser) {
            $admin = create(User::class, ['is_admin' => true]);
            $user = create(User::class, ['is_admin' => false]);
            $this->assertFalse($user->fresh()->isAdmin());

            $browser->loginAs($admin)
                    ->visit(route('admin.users'))
                    ->assertSee($user->username)
                    ->click("span#admintoggle-{$user->id}.icon.has-text-grey-light")
                    ->assertSourceHas('has-text-success')
                    ->pause(100);
            $this->assertTrue($user->fresh()->isAdmin());

            $browser->visit(route('admin.users'))
                    ->assertSee($user->username)
                    ->assertSourceHas('has-text-success')
                    ->click("span#admintoggle-{$user->id}.icon.has-text-success")
                    ->pause(100);
            $this->assertFalse($user->fresh()->isAdmin());
        });
    }

    /** @test */
    public function an_admin_cannot_disable_their_own_admin_status()
    {
        $this->browse(function (Browser $browser) {
            $admin = create(User::class, ['is_admin' => true]);
            $this->assertTrue($admin->fresh()->isAdmin());

            $browser->loginAs($admin)
                    ->visit(route('admin.users'))
                    ->assertSee($admin->username)
                    ->assertSourceMissing('has-text-danger')
                    ->click("span#admintoggle-{$admin->id}.icon.has-text-success")
                    ->pause(100)
                    ->assertSourceHas('has-text-danger');
            $this->assertTrue($admin->fresh()->isAdmin());
        });
    }
}
