<?php

namespace Tests\Browser;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RemoveStudentTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function an_admin_can_remove_students_from_a_course(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = create(User::class, ['is_admin' => true]);
            $course = create(Course::class);
            $student1 = $course->students()->save(create(User::class, ['is_staff' => false]));
            $student2 = $course->students()->save(create(User::class, ['is_staff' => false]));
            $student3 = $course->students()->save(create(User::class, ['is_staff' => false]));

            $browser->loginAs($admin)
                ->visit(route('admin.course.show', $course->id))
                ->assertSee($student1->full_name)
                ->assertSee($student2->full_name)
                ->assertSee($student3->full_name)
                ->press("#remove-student-{$student1->id}")
                ->pause(400)
                ->assertSee('Really Remove')
                ->press("#remove-student-{$student1->id}")
                ->pause(400)
                ->assertDontSee($student1->full_name)
                ->press('Remove All Students')
                ->pause(100)
                ->assertSee('Do you really want to remove all students')
                ->press('Confirm')
                ->pause(300)
                ->assertDontSee($student2->full_name)
                ->assertDontSee($student3->full_name);
        });
    }
}
