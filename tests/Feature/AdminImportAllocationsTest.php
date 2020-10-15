<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Ohffs\SimpleSpout\ExcelSheet;
use Tests\TestCase;

class AdminImportAllocationsTest extends TestCase
{
    /** @test */
    public function regular_users_cant_bulk_import_student_allocations()
    {
        $user = create(User::class);

        $response = $this->actingAs($user)->post(route('project.import.allocations'), [
            'sheet' => UploadedFile::fake()->create('spreadsheet.xlsx', 1),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    /** @test */
    public function admins_can_see_the_bulk_import_allocations_page()
    {
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('project.import.allocations-page'));

        $response->assertOk();
        $response->assertSee('Import Student Project Allocations');
    }

    /** @test */
    public function admins_can_bulk_import_student_allocations()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $student1 = create(User::class, ['is_staff' => false, 'username' => '1234567a']);
        $student2 = create(User::class, ['is_staff' => false, 'username' => '7654321b']);
        $student3 = create(User::class, ['is_staff' => false, 'username' => '9191919c']);
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        $data = [
            ['GUID', 'Name', 'Project ID'],
            ['1234567A', 'Jenny Smith', $project1->id],
            ['7654321b', 'Emma Peel', $project1->id],
            ['9191919d', 'Cathy Gale', $project2->id],
            ['9191919c', 'Cathy Gale', $project2->id],
            ['9191919e', 'Cathy Gale', $project2->id],
        ];

        $filename = (new ExcelSheet)->generate($data);

        $response = $this->actingAs($admin)->post(route('project.import.allocations'), [
            'sheet' => new UploadedFile($filename, 'project_allocations.xlsx', 'application/octet-stream', UPLOAD_ERR_OK, true),
        ]);

        $response->assertStatus(302);
        $this->assertCount(2, $project1->students);
        $this->assertCount(1, $project2->students);
        $this->assertTrue($student1->isAcceptedOn($project1));
        $this->assertTrue($student2->isAcceptedOn($project1));
        $this->assertTrue($student3->isAcceptedOn($project2));
    }

    /** @test */
    public function any_projects_which_are_not_found_are_flashed_back_to_the_user()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $student1 = create(User::class, ['is_staff' => false, 'username' => '1234567a']);
        $student2 = create(User::class, ['is_staff' => false, 'username' => '7654321a']);
        $project1 = create(Project::class);
        $data = [
            ['GUID', 'Name', 'Project ID'],
            ['1234567a', 'Jenny Smith', $project1->id],
            ['7654321b', 'Emma Peel', 9999999],
        ];
        $filename = (new ExcelSheet)->generate($data);

        $response = $this->actingAs($admin)->post(route('project.import.allocations'), [
            'sheet' => new UploadedFile($filename, 'project_allocations.xlsx', 'application/octet-stream', UPLOAD_ERR_OK, true),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $response->assertSessionHasErrors('projectnotfound-9999999');
        $this->assertCount(1, $project1->students);
    }

    /** @test */
    public function any_student_who_are_not_found_are_flashed_back_to_the_user()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $student1 = create(User::class, ['is_staff' => false, 'username' => '1234567a']);
        $project1 = create(Project::class);
        $data = [
            ['GUID', 'Name', 'Project ID'],
            ['1234567a', 'Jenny Smith', $project1->id],
            ['7654321b', 'Emma Peel', $project1->id],
        ];
        $filename = (new ExcelSheet)->generate($data);

        $response = $this->actingAs($admin)->post(route('project.import.allocations'), [
            'sheet' => new UploadedFile($filename, 'project_allocations.xlsx', 'application/octet-stream', UPLOAD_ERR_OK, true),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $response->assertSessionHasErrors('usernotfound-7654321b');
        $this->assertCount(1, $project1->students);
    }
}
