<?php

namespace Tests\Browser;

use App\Project;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProjectBulkOptionsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function admins_can_bulk_edit_project_options()
    {
        $this->browse(function (Browser $browser) {
            $admin = create(User::class, ['is_admin' => true]);
            $project1 = create(Project::class, ['category' => 'undergrad', 'is_active' => true]);
            $project2 = create(Project::class, ['category' => 'undergrad', 'is_active' => false]);
            $project3 = create(Project::class, ['category' => 'undergrad', 'is_active' => false]);
            $project4 = create(Project::class, ['category' => 'undergrad']);

            $browser->loginAs($admin)
                ->visit(route('admin.project.bulk-options.update', ['category' => 'undergrad']))
                ->assertSee($project1->title)
                ->assertSee($project2->title)
                ->assertSee($project3->title)
                ->assertSee($project4->title)
                ->uncheck('#active'.$project1->id)
                ->check('#active'.$project2->id)
                ->check('#delete'.$project4->id)
                ->press('Save Changes')
                ->waitFor('.modal')
                ->press('Confirm')
                ->pause(300)
                ->assertDontSee($project4->title);
            $this->assertFalse($project1->fresh()->isActive());
            $this->assertTrue($project2->fresh()->isActive());
            $this->assertFalse($project3->fresh()->isActive());
        });
    }
}
