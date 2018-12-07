<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Course;
use App\Project;
use Tests\TestCase;
use Box\Spout\Common\Type;
use Ohffs\SimpleSpout\ExcelSheet;
use App\Exports\StaffListExporter;
use Box\Spout\Reader\ReaderFactory;
use App\Exports\StudentListExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StaffExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_export_the_list_of_staff_as_excel_sheet()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $staff1 = create(User::class, ['is_staff' => true]);

        $response = $this->actingAs($admin)->get(route('export.staff.excel'));

        $response->assertOk();
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertEquals('attachment; filename=uog_project_staff.xlsx', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function the_student_exporter_produces_the_expected_data()
    {
        $course = create(Course::class);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $course->students()->saveMany([$student1, $student2]);
        $staff1 = create(User::class, ['is_staff' => true, 'surname' => 'Aaa']);
        $staff2 = create(User::class, ['is_staff' => true, 'surname' => 'Bbb']);
        $project1 = create(Project::class, ['category' => 'undergrad', 'staff_id' => $staff1->id, 'max_students' => 1]);
        $project2 = create(Project::class, ['category' => 'undergrad', 'staff_id' => $staff1->id, 'max_students' => 1]);
        $project3 = create(Project::class, ['category' => 'postgrad', 'staff_id' => $staff1->id, 'max_students' => 2]);
        $project4 = create(Project::class, ['category' => 'undergrad', 'staff_id' => $staff2->id, 'max_students' => 2]);
        $project5 = create(Project::class, ['category' => 'undergrad', 'staff_id' => $staff2->id, 'max_students' => 1]);
        $project1->addAndAccept($student1);
        $student2->projects()->sync([
            $project1->id => ['choice' => 1],
            $project2->id => ['choice' => 2],
            $project3->id => ['choice' => 3],
            $project4->id => ['choice' => 4],
            $project5->id => ['choice' => 5],
        ]);

        $staffList = User::staff()
            ->with(['staffProjects.students', 'secondSupervisorProjects'])
            ->orderBy('surname')
            ->get()
            ->map(function ($user) {
                return $user->getProjectStats();
            });

        $filename = (new StaffListExporter($staffList))->create();

        $contents = (new ExcelSheet)->import($filename);

        // we have 3 rows - two staffmembers + header
        $this->assertCount(3, $contents);
        // dd($contents);
        // the first data row should be $staff1
        tap($contents[1], function ($row) use ($staff1) {
            $this->assertEquals($staff1->username, $row[0]);
            $this->assertEquals($staff1->surname, $row[1]);
            $this->assertEquals($staff1->forenames, $row[2]);
            $this->assertEquals($staff1->email, $row[3]);
            $this->assertEquals(2, $row[4]); // active undergrad
            $this->assertEquals(2, $row[5]); // fully allocated undergrad
            $this->assertEquals(1, $row[6]); // active postgrad
            $this->assertEquals(0, $row[7]); // fully allocated postgrad
            $this->assertEquals(0, $row[8]); // same for 2nd supervision but too lazy to set up test :-/
            $this->assertEquals(0, $row[9]);
            $this->assertEquals(0, $row[10]);
            $this->assertEquals(0, $row[11]);
        });

        // the second data row should be $staff2
        tap($contents[2], function ($row) use ($staff2) {
            $this->assertEquals($staff2->username, $row[0]);
            $this->assertEquals($staff2->surname, $row[1]);
            $this->assertEquals($staff2->forenames, $row[2]);
            $this->assertEquals($staff2->email, $row[3]);
            $this->assertEquals(2, $row[4]);
            $this->assertEquals(1, $row[5]);
            $this->assertEquals(0, $row[6]);
            $this->assertEquals(0, $row[7]);
            $this->assertEquals(0, $row[8]);
            $this->assertEquals(0, $row[9]);
            $this->assertEquals(0, $row[10]);
            $this->assertEquals(0, $row[11]);
        });
    }
}
