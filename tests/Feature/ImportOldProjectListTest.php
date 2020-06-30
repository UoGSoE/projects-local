<?php

namespace Tests\Feature;

use App\User;
use App\Project;
use Tests\TestCase;
use Ohffs\SimpleSpout\ExcelSheet;
use App\Jobs\ImportOldProjectList;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
                        "ItemType" => "Project",
                        "AcademicSession" => "2019/2020",
                        "Code" => "PROJ310020",
                        "Title" => "Autophage launch vehicle structures",
                        "Submitter" => [
                            "GUID" => "pgh6x",
                            "Surname" => "Harkness",
                            "Forenames" => "Patrick",
                        ],
                        "Courses" => [
                            0 => "ENG4110P",
                            1 => "ENG5041P",
                        ],
                        "Staff" => [
                            "pgh6x" => [
                                "GUID" => "pgh6x",
                                "Surname" => "Harkness",
                                "Forenames" => "Patrick",
                            ],
                        ],
                        "Students" => [],
                        "Approved" => "Yes",
                        "ApprovedBy" => "",
                        "Type" => "",
                        "ProjectType" => "FYP",
                        "ConfidentialFlag" => null,
                        "Programme" => "Mechanical Engineering [MEng]|Mechanical Engineering with Aeronautics [MEng]",
                        "Description" => "
                            Autophage rockets offer the possibility of more efficient access to space because the fuselage is used as propellant, with no dry mass left over.\r\n,
                            \r\n,
                            This means that the fuel must have good physical (for strength) and chemical (for energy) properties, with the structure being driven by combustion, not mechanical optimisation. The entire vehicle must withstand acceleration, vibration, and aerodynamic loads.\r\n,
                            \r\n,
                            This vehicle type has been proposed, but no one knows if it is possible. Would you like to start to explore this, in a blend of chemical and FE packages?,
                        ",
                        "Prereq" => "",
                        "NumStudents" => 1,
                        "Placement" => null,
                        "StartDate" => "",
                        "EndDate" => "",
                        "_id" => [
                            "\$id" => "5eea00b2e17578477b0001a6",
                        ],
                    ]
                ]
            ], 200, []),
        ]);
        $admin = create(User::class, ['is_staff' => true, 'is_admin' => true]);
        $fakeSheetData = [
            [],
            ['Autophage launch vehicle structures', 'pgh6x Patrick Harkness /', 'pgh6x', 'Patrick Harkness /', '1', 'ENG4110P / ENG5041P /', 'Biomedical Engineering [MEng]|Biomedical Engineering [BEng]|Electronic & Software Engineering [MEng]|Electronic & Software Engineering [BEng]|Electronics & Electrical Engineering [MEng]|Electronics & Electrical Engineering [BEng]|Mechatronics [MEng]|Mechatronics [BEng]'],
        ];

        ImportOldProjectList::dispatch($fakeSheetData);

        tap(Project::first(), function ($project) {
            $this->assertEquals('Development of Brain Computer Interface with Functctional Electrical Stimulation system', $project->title);
        });
    }
}
