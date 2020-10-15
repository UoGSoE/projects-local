<?php

namespace Tests\Unit;

use App\Models\Programme;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProgrammeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_programme_knows_how_many_total_project_places_are_available_to_students()
    {
        $programme = create(Programme::class);
        $project1 = create(Project::class, ['max_students' => 3]);
        $project2 = create(Project::class, ['max_students' => 4]);
        $project3 = create(Project::class, ['max_students' => 2]);
        $programme->projects()->sync([$project1->id, $project2->id]);

        $this->assertEquals(7, $programme->places_count);
    }

    /** @test */
    public function a_programme_knows_how_many_accepted_students_there_are_for_its_projects()
    {
        $programme = create(Programme::class);
        $project1 = create(Project::class, ['max_students' => 3]);
        $project2 = create(Project::class, ['max_students' => 4]);
        $project3 = create(Project::class, ['max_students' => 2]);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $student3 = create(User::class, ['is_staff' => false]);
        $student1->projects()->sync([$project1->id => ['choice' => 1, 'is_accepted' => true]]);
        $student2->projects()->sync([$project1->id => ['choice' => 2, 'is_accepted' => true]]);
        $student3->projects()->sync([$project3->id => ['choice' => 2, 'is_accepted' => true]]);
        $programme->projects()->sync([$project1->id, $project2->id]);

        $this->assertEquals(2, $programme->accepted_count);
    }
}
