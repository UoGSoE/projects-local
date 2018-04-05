<?php

namespace Tests\Browser;

use App\User;
use App\Course;
use App\Project;
use App\Programme;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StudentApplicationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_student_can_apply_for_projects()
    {
        $this->browse(function (Browser $browser) {
            $student = create(User::class, ['is_staff' => false]);

            $course = create(Course::class, ['category' => 'undergrad']);
            $course->students()->save($student);

            $project1 = create(Project::class, ['category' => 'undergrad']);
            $project2 = create(Project::class, ['category' => 'undergrad']);
            $project3 = create(Project::class, ['category' => 'undergrad']);
            $project4 = create(Project::class, ['category' => 'undergrad']);
            $project5 = create(Project::class, ['category' => 'undergrad']);
            $project6 = create(Project::class, ['category' => 'undergrad']);
            $project7 = create(Project::class, ['category' => 'undergrad']);
            $project8 = create(Project::class, ['category' => 'undergrad']);
            $project9 = create(Project::class, ['category' => 'undergrad']);
            $project10 = create(Project::class, ['category' => 'undergrad']);

            $programme1 = create(Programme::class, ['category' => 'undergrad']);
            $programme2 = create(Programme::class, ['category' => 'undergrad']);

            $course->projects()->sync([$project1->id, $project2->id, $project3->id, $project4->id,  $project5->id, $project6->id, $project7->id, $project8->id, $project9->id, $project10->id]);
            $programme1->projects()->sync([$project1->id, $project2->id]);
            $programme2->projects()->sync([$project3->id, $project4->id]);

            config(['projects.required_choices' => 5]);
            $browser->loginAs($student)
                    ->visit('/')
                    ->assertSee('Available Projects')
                    ->assertDontSee('you can now submit your choices')
                    ->assertDontSee('1 project')
                    ->assertSee($project1->title)
                    ->select('programmes', $programme2->title)
                    ->pause(300)
                    ->assertDontSee($project1->title)
                    ->select('programmes', -1)
                    ->pause(300)
                    ->assertSee($project1->title)
                    ->click("#expand-{$project1->id}")
                    ->click("#project-{$project1->id}-first")
                    ->waitFor('.message-body')
                    ->assertSee('1 project')
                    ->click("#expand-{$project2->id}")
                    ->click("#project-{$project2->id}-second")
                    ->assertSee('2 project')
                    ->click("#expand-{$project3->id}")
                    ->click("#project-{$project3->id}-third")
                    ->assertSee('3 project')
                    ->click("#expand-{$project4->id}")
                    ->click("#project-{$project4->id}-fourth")
                    ->assertSee('4 project')
                    ->click("#expand-{$project5->id}")
                    ->click("#project-{$project5->id}-fifth")
                    ->assertSee('you can now submit your choices')
                    ->press('Submit my choices')
                    ->waitForReload()
                    ->assertPathIs('/thank-you')
                    ->assertSee('Your project choices have been submitted');
        });
    }

    /** @test */
    public function a_student_cant_apply_for_projects_if_they_are_already_accepted()
    {
        $this->browse(function (Browser $browser) {
            $student = create(User::class, ['is_staff' => false]);

            $course = create(Course::class);
            $course->students()->save($student);

            $project1 = create(Project::class);

            $student->projects()->sync([$project1->id => ['choice' => 1, 'is_accepted' => 1]]);

            $course->projects()->sync([$project1->id]);

            config(['projects.required_choices' => 1]);

            $browser->loginAs($student)
                    ->visit('/')
                    ->assertSee('Available Projects')
                    ->assertMissing("#expand-{$project1->id}")
                    ->assertSee('you have already been accepted onto the project');
        });
    }
}
