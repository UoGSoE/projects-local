<?php

namespace Tests\Browser;

use App\Models\Course;
use App\Models\Programme;
use App\Models\Project;
use App\Models\ResearchArea;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudentApplicationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_postgrad_student_can_apply_for_projects()
    {
        // NOTE: postgrad students must pick a 'research area' before they chose their projects
        $this->browse(function (Browser $browser) {
            $student = create(User::class, ['is_staff' => false]);

            $course = create(Course::class, ['category' => 'postgrad']);
            $course->students()->save($student);

            $area1 = create(ResearchArea::class);
            $area2 = create(ResearchArea::class);

            $project1 = create(Project::class, ['category' => 'postgrad']);
            $project2 = create(Project::class, ['category' => 'postgrad']);
            $project3 = create(Project::class, ['category' => 'postgrad']);
            $project4 = create(Project::class, ['category' => 'postgrad']);
            $project5 = create(Project::class, ['category' => 'postgrad']);
            $project6 = create(Project::class, ['category' => 'postgrad']);
            $project7 = create(Project::class, ['category' => 'postgrad']);
            $project8 = create(Project::class, ['category' => 'postgrad']);
            $project9 = create(Project::class, ['category' => 'postgrad']);
            $project10 = create(Project::class, ['category' => 'postgrad']);

            $programme1 = create(Programme::class, ['category' => 'postgrad']);
            $programme2 = create(Programme::class, ['category' => 'postgrad']);

            $course->projects()->sync([$project1->id, $project2->id, $project3->id, $project4->id, $project5->id, $project6->id, $project7->id, $project8->id, $project9->id, $project10->id]);
            $programme1->projects()->sync([$project1->id, $project2->id]);
            $programme2->projects()->sync([$project3->id, $project4->id]);

            config(['projects.required_choices' => 5]);
            $browser->loginAs($student)
                ->visit('/')
                ->assertSee('Available Projects')
                ->assertDontSee('Now choose your projects')
                ->select('research_area', $area1->title)
                ->pause(100)
                ->assertSee('Now choose your projects')
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

            $this->assertEquals(5, $student->projects()->count());
            $this->assertEquals($area1->title, $student->fresh()->research_area);
        });
    }

    /** @test */
    public function an_undergrad_student_can_apply_for_projects()
    {
        // NOTE: undergrad students don't pick a 'research area' before they chose their projects - it defaults it 'N/A'
        $this->browse(function (Browser $browser) {
            $student = create(User::class, ['is_staff' => false]);

            $course = create(Course::class, ['category' => 'undergrad']);
            $course->students()->save($student);

            $area1 = create(ResearchArea::class);
            $area2 = create(ResearchArea::class);

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

            $course->projects()->sync([$project1->id, $project2->id, $project3->id, $project4->id, $project5->id, $project6->id, $project7->id, $project8->id, $project9->id, $project10->id]);
            $programme1->projects()->sync([$project1->id, $project2->id]);
            $programme2->projects()->sync([$project3->id, $project4->id]);

            config(['projects.required_choices' => 5]);
            $browser->loginAs($student)
                ->visit('/')
                ->assertSee('Available Projects')
                ->assertSee('Now choose your projects')
                ->assertDontSee('choose a research theme')
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

            $this->assertEquals(5, $student->projects()->count());
            $this->assertEquals('N/A', $student->fresh()->research_area);
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

    /** @test */
    public function a_student_cant_apply_for_projects_if_the_deadline_has_passed()
    {
        $this->browse(function (Browser $browser) {
            $student = create(User::class, ['is_staff' => false]);

            $course = create(Course::class, ['application_deadline' => now()->subWeeks(2), 'category' => 'postgrad']);
            $course->students()->save($student);

            $project1 = create(Project::class);

            $course->projects()->sync([$project1->id]);

            $area1 = create(ResearchArea::class);
            $area2 = create(ResearchArea::class);

            config(['projects.required_choices' => 1]);

            $browser->loginAs($student)
                ->visit('/')
                ->assertSee('Available Projects')
                ->assertSee('the application deadline has passed')
                ->select('research_area', $area1->title)
                ->pause(100)
                ->click("#expand-{$project1->id}")
                ->click("#project-{$project1->id}-first")
                ->pause(300)
                ->assertSourceMissing('.message-body')
                ->assertDontSee('1 project');
        });
    }

    /** @test */
    public function an_admin_can_impersonate_a_student_and_apply_for_projects_even_if_the_deadline_has_passed()
    {
        $this->browse(function (Browser $browser) {
            $admin = create(User::class, ['is_staff' => true, 'is_admin' => true]);
            $student = create(User::class, ['is_staff' => false]);

            $course = create(Course::class, ['application_deadline' => now()->subWeeks(2), 'category' => 'postgrad']);
            $course->students()->save($student);

            $project1 = create(Project::class);
            config(['projects.required_choices' => 1]);

            $area1 = create(ResearchArea::class);
            $area2 = create(ResearchArea::class);

            $course->projects()->sync([$project1->id]);

            $browser->loginAs($admin)
                ->visit(route('admin.user.show', $student->id))
                ->press('Impersonate')
                ->pause(300)
                ->assertSee('Available Projects')
                ->select('research_area', $area1->title)
                ->pause(100)
                ->click("#expand-{$project1->id}")
                ->click("#project-{$project1->id}-first")
                ->waitFor('.message-body')
                ->assertSee('1 project');
        });
    }
}
