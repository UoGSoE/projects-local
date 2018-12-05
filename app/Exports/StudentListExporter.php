<?php

namespace App\Exports;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;

class StudentListExporter
{
    protected $students;

    public function __construct($students)
    {
        $this->students = $students;
    }

    public function create()
    {
        $filename = tempnam(sys_get_temp_dir(), "student_export");
        $writer = WriterFactory::create(Type::CSV);
        $writer->openToFile($filename);
        $writer->addRows($this->studentsToArray());
        $writer->close();
        return $filename;
    }

    protected function studentsToArray()
    {
        return $this->students->map(function ($student, $key) {
            $choice1 = $student->projects->where('pivot.choice', 1)->first();
            $choice2 = $student->projects->where('pivot.choice', 2)->first();
            $choice3 = $student->projects->where('pivot.choice', 3)->first();
            $choice4 = $student->projects->where('pivot.choice', 4)->first();
            $choice5 = $student->projects->where('pivot.choice', 5)->first();
            return [
                $student->matric,
                $student->surname,
                $student->forenames,
                $student->course->code,
                optional($choice1)->id,
                optional($choice1)->title,
                optional($choice2)->id,
                optional($choice2)->title,
                optional($choice3)->id,
                optional($choice3)->title,
                optional($choice4)->id,
                optional($choice4)->title,
                optional($choice5)->id,
                optional($choice5)->title,
            ];
        })->prepend([
            'Matric',
            'Surname',
            'Forenames',
            'Course',
            '1st-code',
            '1st-title',
            '2nd-code',
            '2nd-title',
            '3rd-code',
            '3rd-title',
            '4th-code',
            '4th-title',
            '5th-code',
            '5th-title',
        ])->toArray();
    }
}
