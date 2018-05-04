<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Project;
use Tests\TestCase;
use App\Exports\ProjectListExporter;
use Illuminate\Support\Facades\Ldap;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class ImportSecondSupervisorsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admins_can_import_a_spreadsheet_to_update_project_second_supervisors()
    {
        // given we have an admin and two members of staff
        $admin = create(User::class, ['is_admin' => true]);
        $sup1 = create(User::class, ['is_staff' => true]);
        $sup2 = create(User::class, ['is_staff' => true]);
        // and we create some projects with those staff as 2nd supervisors
        $project1 = create(Project::class, ['second_supervisor_id' => $sup1->id]);
        $project2 = create(Project::class, ['second_supervisor_id' => $sup1->id]);
        $project3 = create(Project::class, ['second_supervisor_id' => $sup2->id]);
        // and we generate the spreadsheet with those 2nd supervisors in
        $filename = (new ProjectListExporter(Project::all()))->create();
        // and we then clear out those 2nd supervisors from the projects
        $project1->update(['second_supervisor_id' => null]);
        $project2->update(['second_supervisor_id' => null]);
        $project3->update(['second_supervisor_id' => null]);

        // then if we upload the spreadsheet with the supervisors guids in place
        $response = $this->actingAs($admin)->post(route('admin.import.second_supervisors'), [
            'sheet' => new UploadedFile($filename, 'project_list.xlsx', 'application/octet-stream', filesize($filename), UPLOAD_ERR_OK, true),
        ]);

        // they should get added back in as 2nd supervisors
        $response->assertStatus(302);
        $this->assertEquals($sup1->id, $project1->fresh()->second_supervisor_id);
        $this->assertEquals($sup1->id, $project2->fresh()->second_supervisor_id);
        $this->assertEquals($sup2->id, $project3->fresh()->second_supervisor_id);
    }
}
