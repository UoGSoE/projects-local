<?php

namespace Tests\Feature;

use App\User;
use App\Course;
use App\Project;
use App\Programme;
use Tests\TestCase;
use App\Imports\OldDataImporter;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImportOldDataTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function we_can_import_old_project_data_from_json_data()
    {
        $jsonString = file_get_contents(__DIR__ . '/data/old_undergrad_projects.json');

        (new OldDataImporter($jsonString))->import();

        $this->assertCount(2, Project::all());
        $this->assertCount(3, Programme::all());
        $this->assertCount(2, Course::all());
        $this->assertCount(2, User::all());
        tap(Project::all()[0], function ($project) {
            $this->assertEquals('MEng A new device to study liquid bridges between particles', $project->title);
            $this->assertEquals('Blah de blah', $project->description);
            $this->assertEquals('Some pre-reqs', $project->pre_req);
            $this->assertEquals(1, $project->max_students);
            $this->assertTrue($project->isPlacement());
            $this->assertFalse($project->isConfidential());
            $this->assertEquals('undergrad', $project->category);
            $this->assertCount(1, $project->courses);
            $this->assertEquals('ENG1234', $project->courses->first()->code);
            $this->assertCount(2, $project->programmes);
            $this->assertEquals('Programme1 [MEng]', $project->programmes[0]->title);
            $this->assertEquals('Programme2 [MEng]', $project->programmes[1]->title);
            $this->assertEquals('fake1x', $project->owner->username);
        });
    }
}
