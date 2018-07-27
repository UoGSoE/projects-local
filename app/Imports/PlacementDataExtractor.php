<?php

namespace App\Imports;

use App\User;
use App\Course;
use App\Programme;
use Illuminate\Support\MessageBag;

class PlacementDataExtractor
{
    protected $row;

    protected $errors;

    public function __construct($row)
    {
        $this->row = $row;
        $this->errors = new MessageBag();
    }

    public function extract()
    {
        $data = $this->extractCells($this->row);

        $data['error'] = false;
        if (!$data['staff'] or !$data['student'] or !$data['course'] or !$data['programme']) {
            $data['error'] = true;
        }

        return $data;
    }

    public function hasErrors()
    {
        return $this->errors->count() > 0;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function extractCells($row)
    {
        $data = [
            'category' => strtolower($row[0]),
            'title' => $row[1],
            'description' => $row[2],
            'prereq' => $row[3],
            'active' => substr(strtolower($row[4]), 0, 1),
            'placement' => substr(strtolower($row[5]), 0, 1),
            'confidential' => substr(strtolower($row[6]), 0, 1),
            'guid' => strtolower($row[7]),
            'max_students' => $row[8],
            'courseCode' => strtoupper($row[9]),
            'programmeName' => $row[10],
            'matric' => $row[11],
            'surname' => strtolower($row[12]),
        ];
        $data['staff'] = $this->findStaff($data['guid']);
        $data['student'] = $this->findStudent($data['matric'], $data['surname']);
        $data['course'] = $this->findCourse($data['courseCode'], $data['category']);
        $data['programme'] = $this->findProgramme($data['programmeName'], $data['category']);
        return $data;
    }

    protected function findStaff($guid)
    {
        $staff = User::where('username', '=', $guid)->first();
        if (!$staff) {
            $this->errors->add("staffnotfound-{$guid}", "Staff Not Found : {$guid}");
        }
        return $staff;
    }

    protected function findStudent($matric, $surname)
    {
        $studentGuid = $matric . strtolower(substr($surname, 0, 1));
        $student = User::where('username', '=', $studentGuid)->first();
        if (!$student) {
            $this->errors->add("studentnotfound-{$studentGuid}", "Student Not Found : {$studentGuid}");
        }
        return $student;
    }

    protected function findCourse($code, $category)
    {
        $course = Course::where('code', '=', $code)->where('category', '=', $category)->first();
        if (!$course) {
            $this->errors->add("coursenotfound-{$code}", "Course Not Found : {$code}");
        }
        return $course;
    }

    protected function findProgramme($title, $category)
    {
        $programme = Programme::where('title', '=', $title)->where('category', '=', $category)->first();
        if (!$programme) {
            $this->errors->add("programmenotfound-{$title}", "Programme Not Found : {$title}");
        }
        return $programme;
    }
}