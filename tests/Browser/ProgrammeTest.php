<?php

namespace Tests\Browser;

use App\Programme;
use App\Project;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProgrammeTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function admins_can_do_all_the_programmes_stuff()
    {
        $this->browse(function (Browser $browser) {
            $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
            $ugradProgramme = create(Programme::class, ['category' => 'undergrad']);
            $pgradProgramme = create(Programme::class, ['category' => 'postgrad']);
            $ugradProjects = create(Project::class, ['category' => 'undergrad'], 2);
            $ugradProgramme->projects()->sync($ugradProjects->pluck('id'));
            $pgradProjects = create(Project::class, ['category' => 'undergrad'], 3);
            $pgradProgramme->projects()->sync($pgradProjects->pluck('id'));

            $browser->loginAs($admin)
                    ->visit(route('admin.programme.index'))
                    ->assertSee('Programmes')
                    ->assertSee($ugradProgramme->title)
                    ->assertSee($pgradProgramme->title)
                    // ->assertSeeIn(".project-count-{$ugradProgramme->id}", "2")
                    // ->assertSeeIn(".project-count-{$pgradProgramme->id}", "3")
                    ->clickLink($ugradProgramme->title)
                    ->assertUrlIs(route('admin.programme.edit', $ugradProgramme->id))
                    ->type('title', 'UPDATED PROGRAMME')
                    ->press('Update Programme')
                    ->assertUrlIs(route('admin.programme.index'))
                    ->assertSee('UPDATED PROGRAMME')
                    ->click('#add-programme')
                    ->assertSee('Create new programme')
                    ->type('title', 'NEW PROGRAMME')
                    ->select('category', 'postgrad')
                    ->press('Create Programme')
                    ->assertUrlIs(route('admin.programme.index'))
                    ->assertSee('NEW PROGRAMME')
                    ->clickLink('NEW PROGRAMME')
                    ->press('Delete Programme')
                    ->assertSee('Do you really want to delete this programme')
                    ->press('Cancel')
                    ->pause(300)
                    ->assertDontSee('Do you really want to delete this programme')
                    ->press('Delete Programme')
                    ->press('Confirm')
                    ->pause(200)
                    ->assertUrlIs(route('admin.programme.index'))
                    ->assertDontSee('NEW PROGRAMME');
        });
    }
}
