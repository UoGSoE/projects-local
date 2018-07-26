<?php

namespace Tests\Feature;

use App\User;
use App\Course;
use App\Project;
use App\Programme;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectPlacementImportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_see_the_placements_import_page()
    {
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.import.placements.show'));

        $response->assertOk();
        $response->assertSee("Import placement projects");
    }

    /** @test */
    public function an_admin_can_import_a_list_of_placement_projects()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $staff = create(User::class, ['is_staff' => true, 'username' => 'staff1x']);
        $student = create(User::class, ['is_staff' => false, 'username' => '1234567s']);
        $course = create(Course::class, ['code' => 'ENG5041P', 'category' => 'undergrad']);
        $programme = create(Programme::class, ['title' => 'BME', 'category' => 'undergrad']);
        Activity::all()->each->delete();

        $filename = './tests/Feature/data/placement_projects.xlsx';

        $response = $this->actingAs($admin)->post(route('admin.import.placements'), [
            'sheet' => new UploadedFile($filename, 'placements.xlsx', 'application/octet-stream', filesize($filename), UPLOAD_ERR_OK, true),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.import.placements.show'));
        $log = Activity::first();
        $this->assertTrue($log->causer->is($admin));
        $this->assertEquals("Imported placement projects", $log->description);
        $project = Project::first();
        $this->assertEquals('3D Printed Scaffolds for Bone Regeneration', $project->title);
        $this->assertEquals('For XXXXX  XXXXX ONLY - MEng Placement', $project->description);
        $this->assertTrue($project->isActive());
        $this->assertTrue($project->isPlacement());
        $this->assertFalse($project->isConfidential());
    }

    /** @test */
    public function importing_placement_projects_with_an_unrecognised_staff_member_flags_an_error()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $student = create(User::class, ['is_staff' => false, 'username' => '1234567s']);
        $course = create(Course::class, ['code' => 'ENG5041P', 'category' => 'undergrad']);
        $programme = create(Programme::class, ['title' => 'BME', 'category' => 'undergrad']);
        Activity::all()->each->delete();

        $filename = './tests/Feature/data/placement_projects.xlsx';

        $response = $this->actingAs($admin)->post(route('admin.import.placements'), [
            'sheet' => new UploadedFile($filename, 'placements.xlsx', 'application/octet-stream', filesize($filename), UPLOAD_ERR_OK, true),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.import.placements.show'));
        $this->assertCount(0, Project::all());
        $response->assertSessionHasErrors('staffnotfound-staff1x');
    }

    /** @test */
    public function importing_placement_projects_with_an_unrecognised_student_member_flags_an_error()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $staff = create(User::class, ['is_staff' => true, 'username' => 'staff1x']);
        $course = create(Course::class, ['code' => 'ENG5041P', 'category' => 'undergrad']);
        $programme = create(Programme::class, ['title' => 'BME', 'category' => 'undergrad']);
        Activity::all()->each->delete();

        $filename = './tests/Feature/data/placement_projects.xlsx';

        $response = $this->actingAs($admin)->post(route('admin.import.placements'), [
            'sheet' => new UploadedFile($filename, 'placements.xlsx', 'application/octet-stream', filesize($filename), UPLOAD_ERR_OK, true),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.import.placements.show'));
        $this->assertCount(0, Project::all());
        $response->assertSessionHasErrors('studentnotfound-1234567s');
    }

    /** @test */
    public function importing_placement_projects_with_an_unrecognised_course_flags_an_error()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $staff = create(User::class, ['is_staff' => true, 'username' => 'staff1x']);
        $student = create(User::class, ['is_staff' => false, 'username' => '1234567s']);
        $programme = create(Programme::class, ['title' => 'BME', 'category' => 'undergrad']);
        Activity::all()->each->delete();

        $filename = './tests/Feature/data/placement_projects.xlsx';

        $response = $this->actingAs($admin)->post(route('admin.import.placements'), [
            'sheet' => new UploadedFile($filename, 'placements.xlsx', 'application/octet-stream', filesize($filename), UPLOAD_ERR_OK, true),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.import.placements.show'));
        $this->assertCount(0, Project::all());
        $response->assertSessionHasErrors('coursenotfound-ENG5041P');
    }

    /** @test */
    public function importing_placement_projects_with_an_unrecognised_programme_flags_an_error()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $staff = create(User::class, ['is_staff' => true, 'username' => 'staff1x']);
        $student = create(User::class, ['is_staff' => false, 'username' => '1234567s']);
        $course = create(Course::class, ['code' => 'ENG5041P', 'category' => 'undergrad']);
        Activity::all()->each->delete();

        $filename = './tests/Feature/data/placement_projects.xlsx';

        $response = $this->actingAs($admin)->post(route('admin.import.placements'), [
            'sheet' => new UploadedFile($filename, 'placements.xlsx', 'application/octet-stream', filesize($filename), UPLOAD_ERR_OK, true),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.import.placements.show'));
        $this->assertCount(0, Project::all());
        $response->assertSessionHasErrors('programmenotfound-BME');
    }
}
