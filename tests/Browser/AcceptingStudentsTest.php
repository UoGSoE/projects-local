<?php

namespace Tests\Browser;

use App\Models\Course;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AcceptingStudentsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function staff_can_accept_first_choice_students_on_projects_that_allow_it(): void
    {
        $this->browse(function (Browser $browser) {
            $staff = create(User::class, ['is_staff' => true]);
            $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
            $course = create(Course::class, ['allow_staff_accept' => true, 'category' => 'undergrad']);
            $project->courses()->sync([$course->id]);
            $student1 = create(User::class, ['is_staff' => false]);
            $student2 = create(User::class, ['is_staff' => false]);
            $student3 = create(User::class, ['is_staff' => false]);
            $student1->projects()->sync([$project->id => ['choice' => 1, 'is_accepted' => false]]);
            $student2->projects()->sync([$project->id => ['choice' => 2, 'is_accepted' => false]]);

            $browser->loginAs($staff)
                ->visit(route('project.show', $project->id))
                // check the basics
                ->assertSee($project->title)
                ->assertSee($student1->full_name)
                ->assertSee($student2->full_name)
                ->assertDontSee($student3->full_name)
                // check we have the correct html form elements
                ->assertSourceHas('accept-'.$student1->id)
                ->assertSourceMissing('accept-'.$student2->id)
                ->assertDontSeeIn("#status-{$student1->id}", 'Yes')
                ->assertSeeIn("#status-{$student2->id}", 'No')
                // check making changes then undoing them effects the save button
                ->assertDontSee('Save Changes')
                ->check("accept-{$student1->id}")
                ->assertSee('Save Changes')
                ->uncheck("accept-{$student1->id}")
                ->assertDontSee('Save Changes')
                ->check("accept-{$student1->id}")
                // commiting the changes updates things
                ->press('Save Changes')
                ->pause(200)
                ->visit(route('project.show', $project->id))
                ->assertSeeIn("#status-{$student1->id}", 'Yes')
                ->assertSeeIn("#status-{$student2->id}", 'No');
            $this->assertTrue($student1->isAccepted());
            $this->assertFalse($student2->isAccepted());
        });
    }

    /** @test */
    public function staff_cant_accept_anyone_on_projects_that_dont_allow_it(): void
    {
        $this->browse(function (Browser $browser) {
            $staff = create(User::class, ['is_staff' => true]);
            $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
            $course = create(Course::class, ['allow_staff_accept' => false, 'category' => 'undergrad']);
            $project->courses()->sync([$course->id]);
            $student1 = create(User::class, ['is_staff' => false]);
            $student2 = create(User::class, ['is_staff' => false]);
            $student1->projects()->sync([$project->id => ['choice' => 1, 'is_accepted' => true]]);
            $student2->projects()->sync([$project->id => ['choice' => 2, 'is_accepted' => false]]);

            $browser->loginAs($staff)
                ->visit(route('project.show', $project->id))
                ->assertSee($project->title)
                ->assertSee($student1->full_name)
                ->assertSee($student2->full_name)
                ->assertSourceMissing('accept-'.$student1->id)
                ->assertSourceMissing('accept-'.$student2->id)
                ->assertSeeIn("#status-{$student1->id}", 'Yes')
                ->assertSeeIn("#status-{$student2->id}", 'No')
                ->assertDontSee('Save Changes');
        });
    }

    /** @test */
    public function admins_can_accept_anyone_on_any_project(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
            $project = create(Project::class, ['category' => 'postgrad']);
            $student1 = create(User::class, ['is_staff' => false]);
            $student2 = create(User::class, ['is_staff' => false]);
            $student3 = create(User::class, ['is_staff' => false]);
            $student1->projects()->sync([$project->id => ['choice' => 1, 'is_accepted' => false]]);
            $student2->projects()->sync([$project->id => ['choice' => 2, 'is_accepted' => false]]);

            $browser->loginAs($admin)
                ->visit(route('project.show', $project->id))
                // check the basics
                ->assertSee($project->title)
                ->clickLink($student1->full_name)
                ->assertUrlIs(route('admin.user.show', $student1->id))
                ->visit(route('project.show', $project->id))
                ->assertSeeIn('#student-list-form', $student1->full_name)
                ->assertSeeIn('#student-list-form', $student2->full_name)
                ->assertDontSeeIn('#student-list-form', $student3->full_name)
                // check we have the correct html form elements
                ->assertSourceHas('accept-'.$student1->id)
                ->assertSourceHas('accept-'.$student2->id)
                // check making changes then undoing them effects the save button
                ->assertDontSee('Save Changes')
                ->check("accept-{$student1->id}")
                ->assertSee('Save Changes')
                ->uncheck("accept-{$student1->id}")
                ->assertDontSee('Save Changes')
                ->check("accept-{$student2->id}")
                // commiting the changes updates things
                ->press('Save Changes')
                ->pause(200)
                ->visit(route('project.show', $project->id))
                ->assertSourceHas('accept-'.$student1->id)
                ->assertSourceHas('accept-'.$student2->id);
            $this->assertTrue($student2->isAccepted());
            $this->assertFalse($student1->isAccepted());
        });
    }

    /** @test */
    public function admins_can_manually_add_and_accept_anyone_on_any_project(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
            $project = create(Project::class, ['category' => 'postgrad']);
            $student1 = create(User::class, ['is_staff' => false]);
            $student2 = create(User::class, ['is_staff' => false]);
            $student2->projects()->sync([$project->id => ['choice' => 2, 'is_accepted' => false]]);

            $browser->loginAs($admin)
                ->visit(route('project.show', $project->id))
                ->assertSourceMissing('accept-'.$student1->id)
                ->assertSourceHas('accept-'.$student2->id)
                ->assertSeeIn('.select', $student1->full_name)
                ->select('student_id', $student1->id)
                ->press('Allocate & Accept')
                ->pause(200)
                ->visit(route('project.show', $project->id))
                ->assertSourceHas('accept-'.$student1->id)
                ->assertSourceHas('accept-'.$student2->id);
            $this->assertTrue($student1->isAccepted());
            $this->assertFalse($student2->isAccepted());
        });
    }
}
