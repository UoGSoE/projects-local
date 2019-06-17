<?php
// @codingStandardsIgnoreFile
namespace Tests\Feature\Admin;

use App\User;
use App\Course;
use App\Project;
use Tests\TestCase;
use App\Exports\StaffExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StaffExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_staff_exporter_produces_the_expected_data()
    {
        Excel::fake();
        $admin = create(User::class, ['is_admin' => true, 'surname' => 'Ccc']);
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

        $this->actingAs($admin)->get(route('export.staff', 'xlsx'));

        Excel::assertDownloaded('uog_project_staff.xlsx', function (StaffExport $export) use ($admin, $staff1, $staff2) {
            //4 rows, headers, 2 staff and an admin
            $this->assertCount(4, $export->collection());

            $this->assertEquals($staff1->username, $export->collection()[1]['username']);
            $this->assertEquals($staff1->surname, $export->collection()[1]['surname']);
            $this->assertEquals($staff1->forenames, $export->collection()[1]['forenames']);
            $this->assertEquals($staff1->email, $export->collection()[1]['email']);
            $this->assertEquals(2, $export->collection()[1]['ugrad_active']);
            $this->assertEquals(2, $export->collection()[1]['ugrad_allocated']);
            $this->assertEquals(1, $export->collection()[1]['pgrad_active']);
            $this->assertEquals(0, $export->collection()[1]['pgrad_allocated']);
            $this->assertEquals(0, $export->collection()[1]['2nd_ugrad_active']);
            $this->assertEquals(0, $export->collection()[1]['2nd_ugrad_allocated']);
            $this->assertEquals(0, $export->collection()[1]['2nd_pgrad_active']);
            $this->assertEquals(0, $export->collection()[1]['2nd_pgrad_allocated']);

            $this->assertEquals($staff2->username, $export->collection()[2]['username']);
            $this->assertEquals($staff2->surname, $export->collection()[2]['surname']);
            $this->assertEquals($staff2->forenames, $export->collection()[2]['forenames']);
            $this->assertEquals($staff2->email, $export->collection()[2]['email']);
            $this->assertEquals(2, $export->collection()[2]['ugrad_active']);
            $this->assertEquals(1, $export->collection()[2]['ugrad_allocated']);
            $this->assertEquals(0, $export->collection()[2]['pgrad_active']);
            $this->assertEquals(0, $export->collection()[2]['pgrad_allocated']);
            $this->assertEquals(0, $export->collection()[2]['2nd_ugrad_active']);
            $this->assertEquals(0, $export->collection()[2]['2nd_ugrad_allocated']);
            $this->assertEquals(0, $export->collection()[2]['2nd_pgrad_active']);
            $this->assertEquals(0, $export->collection()[2]['2nd_pgrad_allocated']);

            $this->assertEquals($admin->username, $export->collection()[3]['username']);
            $this->assertEquals($admin->surname, $export->collection()[3]['surname']);
            $this->assertEquals($admin->forenames, $export->collection()[3]['forenames']);
            $this->assertEquals($admin->email, $export->collection()[3]['email']);
            $this->assertEquals(0, $export->collection()[3]['ugrad_active']);
            $this->assertEquals(0, $export->collection()[3]['ugrad_allocated']);
            $this->assertEquals(0, $export->collection()[3]['pgrad_active']);
            $this->assertEquals(0, $export->collection()[3]['pgrad_allocated']);
            $this->assertEquals(0, $export->collection()[3]['2nd_ugrad_active']);
            $this->assertEquals(0, $export->collection()[3]['2nd_ugrad_allocated']);
            $this->assertEquals(0, $export->collection()[3]['2nd_pgrad_active']);
            $this->assertEquals(0, $export->collection()[3]['2nd_pgrad_allocated']);

            return true;
        });
    }
}
