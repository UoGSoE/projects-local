<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Imports\OldDataImporter;
use App\Models\Programme;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportOldDataTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function we_can_import_old_project_data_from_json_data()
    {
        $jsonString = file_get_contents(__DIR__.'/data/old_undergrad_projects.json');

        (new OldDataImporter($jsonString))->import();

        $this->assertCount(3, Project::all());
        $this->assertCount(6, Programme::all());
        $this->assertCount(3, Course::all());
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
        tap(Project::all()[1], function ($project) {
            $this->assertEquals('(BEng) Design and Build an Optical Fibre Pulling System', $project->title);
            $this->assertEquals('Some amazing project description', $project->description);
            $this->assertEquals('', $project->pre_req);
            $this->assertEquals(1, $project->max_students);
            $this->assertFalse($project->isPlacement());
            $this->assertFalse($project->isConfidential());
            $this->assertEquals('undergrad', $project->category);
            $this->assertCount(1, $project->courses);
            $this->assertEquals('ENG2345', $project->courses->first()->code);
            $this->assertCount(2, $project->programmes);
            $this->assertEquals('Programme1 [MEng]', $project->programmes[0]->title);
            $this->assertEquals('Programme3 [MEng]', $project->programmes[1]->title);
            $this->assertEquals('fake2x', $project->owner->username);
        });
        tap(Project::all()[2], function ($project) {
            $this->assertEquals('xAP enabled 1-Wire master', $project->title);
            $this->assertEquals('Something to do with busses', $project->description);
            $this->assertEquals('Competent programming skills', $project->pre_req);
            $this->assertEquals(1, $project->max_students);
            $this->assertFalse($project->isPlacement());
            $this->assertFalse($project->isConfidential());
            $this->assertEquals('postgrad', $project->category);
            $this->assertCount(1, $project->courses);
            $this->assertEquals('ENG9191', $project->courses->first()->code);
            $this->assertCount(3, $project->programmes);
            $this->assertEquals('PG Programme1 [MSc]', $project->programmes[0]->title);
            $this->assertEquals('PG Programme2 [MSc]', $project->programmes[1]->title);
            $this->assertEquals('PG Programme3 [MSc]', $project->programmes[2]->title);
            $this->assertEquals('fake1x', $project->owner->username);
        });
    }

    /** @test */
    public function we_can_run_an_artisan_command_to_import_the_data()
    {
        $filename = __DIR__.'/data/old_undergrad_projects.json';

        Artisan::call('projects:import-old', ['filename' => $filename]);

        $this->assertCount(3, Project::all());
        $this->assertCount(6, Programme::all());
        $this->assertCount(3, Course::all());
        $this->assertCount(2, User::all());
    }
}
