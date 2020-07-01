<?php

namespace Tests\Feature;

use App\Course;
use App\User;
use App\Project;
use Tests\TestCase;
use App\Jobs\ImportOldProjectList;
use App\Programme;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Ohffs\SimpleSpout\ExcelSheet;

class ImportOldProjectListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function we_can_import_old_data_from_the_wlm_based_on_an_array_of_data()
    {
        Http::fake([
            config('projects.wlm_api_url').'*' => Http::response([
                'Data' => [
                    [
                        "Title" => "Autophage launch vehicle structures",
                        "Programme" => "Mechanical Engineering [MEng]|Mechanical Engineering with Aeronautics [MEng]",
                        "Description" => "Blah de blah",
                        "Prereq" => "",
                    ],
                    [
                        "Title" => "22Autophage launch vehicle structures",
                        "Programme" => "22Mechanical Engineering [MEng]|Mechanical Engineering with Aeronautics [MEng]",
                        "Description" => "22Blah de blah",
                        "Prereq" => "22",
                    ],
                ]
            ], 200, []),
        ]);
        $admin = create(User::class, ['is_staff' => true, 'is_admin' => true]);
        $fakeSheetData = [
            [],
            ['Autophage launch vehicle structures', 'pgh6x Patrick Harkness /', 'pgh6x', 'Patrick Harkness /', '1', '', 'ENG4110P / ENG5041P /', 'Biomedical Engineering [MEng]|Biomedical Engineering [BEng]|Electronic & Software Engineering [MEng]|Electronic & Software Engineering [BEng]|Electronics & Electrical Engineering [MEng]|Electronics & Electrical Engineering [BEng]|Mechatronics [MEng]|Mechatronics [BEng]'],
            ['22Autophage launch vehicle structures', 'pgh6x Patrick Harkness /', 'pgh6x', 'Patrick Harkness /', '1', '', 'ENG4110P / ENG5041P / ENG1234', '22Biomedical Engineering [MEng]|Biomedical Engineering [BEng]|Electronic & Software Engineering [MEng]|Electronic & Software Engineering [BEng]|Electronics & Electrical Engineering [MEng]|Electronics & Electrical Engineering [BEng]|Mechatronics [MEng]|Mechatronics [BEng]'],
        ];

        ImportOldProjectList::dispatch($fakeSheetData);

        tap(Project::find(1), function ($project) {
            $this->assertEquals('Autophage launch vehicle structures', $project->title);
            $this->assertEquals('Blah de blah', $project->description);
            $this->assertEquals('pgh6x', $project->owner->username);
            $this->assertEquals(2, $project->courses()->count());
            $this->assertEquals(8, $project->programmes()->count());
        });
        tap(Project::find(2), function ($project) {
            $this->assertEquals('22Autophage launch vehicle structures', $project->title);
            $this->assertEquals('22Blah de blah', $project->description);
            $this->assertEquals('pgh6x', $project->owner->username);
            $this->assertEquals(3, $project->courses()->count());
            $this->assertEquals(8, $project->programmes()->count());
        });
        $this->assertEquals(3, Course::count());
        // ENG4110P / ENG5041P / ENG1234
        $this->assertDatabaseHas('courses', ['code' => 'ENG4110P']);
        $this->assertDatabaseHas('courses', ['code' => 'ENG5041P']);
        $this->assertDatabaseHas('courses', ['code' => 'ENG1234']);
        $this->assertEquals(9, Programme::count());
        $this->assertEquals(2, User::count());
    }

    /** @test */
    public function admins_can_upload_a_spreadsheet_which_kicks_off_the_import()
    {
        $this->withoutExceptionHandling();
        Bus::fake();
        $admin = create(User::class, ['is_staff' => true, 'is_admin' => true]);
        $fakeSheetData = [
            [],
            ['Autophage launch vehicle structures', 'pgh6x Patrick Harkness /', 'pgh6x', 'Patrick Harkness /', '1', 'ENG4110P / ENG5041P /', 'Biomedical Engineering [MEng]|Biomedical Engineering [BEng]|Electronic & Software Engineering [MEng]|Electronic & Software Engineering [BEng]|Electronics & Electrical Engineering [MEng]|Electronics & Electrical Engineering [BEng]|Mechatronics [MEng]|Mechatronics [BEng]'],
            ['22Autophage launch vehicle structures', 'pgh6x Patrick Harkness /', 'pgh6x', 'Patrick Harkness /', '1', 'ENG4110P / ENG5041P / ENG1234', '22Biomedical Engineering [MEng]|Biomedical Engineering [BEng]|Electronic & Software Engineering [MEng]|Electronic & Software Engineering [BEng]|Electronics & Electrical Engineering [MEng]|Electronics & Electrical Engineering [BEng]|Mechatronics [MEng]|Mechatronics [BEng]'],
        ];
        $tmpSheet = (new ExcelSheet)->generate($fakeSheetData);
        $fakeSheet = new UploadedFile($tmpSheet, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($admin)->post(route('import.oldprojects'), [
            'sheet' => $fakeSheet,
        ]);

        $response->assertRedirect();
        Bus::assertDispatched(ImportOldProjectList::class);
    }

    /** @test */
    public function admins_can_see_the_page_to_import_the_projects()
    {
        $admin = create(User::class, ['is_staff' => true, 'is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('import.show_importoldprojects'));

        $response->assertOk();
        $response->assertSee('Import');
    }

    /** @test */
    public function regular_users_cant_see_the_page_to_import_the_projects()
    {
        $admin = create(User::class, ['is_staff' => true, 'is_admin' => false]);

        $response = $this->actingAs($admin)->get(route('import.show_importoldprojects'));

        $response->assertRedirect(route('home'));
    }
}
