<?php

namespace Tests\Feature\Admin;

use App\Course;
use App\Exports\StudentListExporter;
use App\Project;
use App\User;
use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_export_the_list_of_students_as_a_csv()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $course->students()->saveMany([$student1, $student2]);
        $project1 = create(Project::class, ['category' => 'undergrad']);
        $project2 = create(Project::class, ['category' => 'undergrad']);
        $project3 = create(Project::class, ['category' => 'undergrad']);
        $project4 = create(Project::class, ['category' => 'undergrad']);
        $project5 = create(Project::class, ['category' => 'undergrad']);
        $project1->addAndAccept($student1);
        $student2->projects()->sync([
            $project1->id => ['choice' => 1],
            $project2->id => ['choice' => 2],
            $project3->id => ['choice' => 3],
            $project4->id => ['choice' => 4],
            $project5->id => ['choice' => 5],
        ]);

        $response = $this->actingAs($admin)->get(route('export.students.csv', ['category' => 'undergrad']));

        $response->assertOk();
        $this->assertEquals('text/csv', $response->headers->get('content-type'));
        $this->assertEquals('attachment; filename=uog_undergrad_project_students.csv', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function the_student_exporter_produces_the_expected_data()
    {
        $course = create(Course::class);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $course->students()->saveMany([$student1, $student2]);
        $project1 = create(Project::class, ['category' => 'undergrad']);
        $project2 = create(Project::class, ['category' => 'undergrad']);
        $project3 = create(Project::class, ['category' => 'undergrad']);
        $project4 = create(Project::class, ['category' => 'undergrad']);
        $project5 = create(Project::class, ['category' => 'undergrad']);
        $project1->addAndAccept($student1);
        $student2->projects()->sync([
            $project1->id => ['choice' => 1],
            $project2->id => ['choice' => 2],
            $project3->id => ['choice' => 3],
            $project4->id => ['choice' => 4],
            $project5->id => ['choice' => 5],
        ]);

        $filename = (new StudentListExporter(collect([$student1, $student2])))->create();

        $reader = ReaderFactory::create(Type::CSV);
        $reader->open($filename);
        $data = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $data[] = $row;
            }
        }

        // we have 3 rows - two students + header
        $this->assertCount(3, $data);

        // the first data row should be student1
        tap($data[1], function ($row) use ($student1, $project1) {
            $this->assertEquals($student1->matric, $row[0]);
            $this->assertEquals($student1->surname, $row[1]);
            $this->assertEquals($student1->forenames, $row[2]);
            $this->assertEquals($student1->course->code, $row[3]);
            $this->assertEquals($project1->id, $row[4]);
            $this->assertEquals($project1->title, $row[5]);
            $this->assertEquals('', $row[6]);
            $this->assertEquals('', $row[7]);
            $this->assertEquals('', $row[8]);
            $this->assertEquals('', $row[9]);
            $this->assertEquals('', $row[10]);
            $this->assertEquals('', $row[11]);
            $this->assertEquals('', $row[12]);
            $this->assertEquals('', $row[13]);
        });

        // the second data row should be student2
        tap($data[2], function ($row) use ($student2, $project1, $project2, $project3, $project4, $project5) {
            $this->assertEquals($student2->matric, $row[0]);
            $this->assertEquals($student2->surname, $row[1]);
            $this->assertEquals($student2->forenames, $row[2]);
            $this->assertEquals($student2->course->code, $row[3]);
            $this->assertEquals($project1->id, $row[4]);
            $this->assertEquals($project1->title, $row[5]);
            $this->assertEquals($project2->id, $row[6]);
            $this->assertEquals($project2->title, $row[7]);
            $this->assertEquals($project3->id, $row[8]);
            $this->assertEquals($project3->title, $row[9]);
            $this->assertEquals($project4->id, $row[10]);
            $this->assertEquals($project4->title, $row[11]);
            $this->assertEquals($project5->id, $row[12]);
            $this->assertEquals($project5->title, $row[13]);
        });
    }
}
