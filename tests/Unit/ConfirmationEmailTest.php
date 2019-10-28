<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Mail\ChoiceConfirmation;
use App\User;
use App\Project;

class ConfirmationEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_confirmation_email_shows_the_students_project_choices()
    {
        $student = create(User::class, ['is_staff' => false]);
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        $project3 = create(Project::class);
        $student->projects()->sync([
            $project1->id => ['choice' => 2],
            $project2->id => ['choice' => 1]
        ]);

        $mail = new ChoiceConfirmation($student);

        $contents = $mail->render();
        $this->assertStringContainsString($project1->title, $contents);
        $this->assertStringContainsString($project2->title, $contents);
        $this->assertStringNotContainsString($project3->title, $contents);
    }
}
