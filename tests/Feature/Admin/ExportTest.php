<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Project;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_download_a_spreadsheet_of_all_project_data()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $project1->students()->sync([$student1->id => ['choice' => 1]]);
        $project2->students()->sync([$student2->id => ['choice' => 2]]);

        $response = $this->actingAs($admin)->get(route('export.projects.excel'));

        $response->assertSuccessful();
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertEquals('attachment; filename="uog_project_data.xlsx"', $response->headers->get('content-disposition'));
    }

}
