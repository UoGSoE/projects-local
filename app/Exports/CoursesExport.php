<?php

namespace App\Exports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\FromCollection;

class CoursesExport implements FromCollection
{
    public function collection()
    {
        return Course::withCount(['projects', 'students'])
            ->orderBy('code')
            ->get()
            ->map(function ($course, $key) {
                return [
                    'code' => $course['code'],
                    'title' => $course['title'],
                    'type' => $course['type'],
                    'application_deadline' => $course['application_deadline']->format('d/m/Y'),
                    'projects_count' => $course['projects_count'],
                    'students_count' => $course['students_count'],
                ];
            })
            ->prepend([
                'Code',
                'Title',
                'Type',
                'Deadline',
                'No. Projects',
                'No. Students',
            ]);
    }
}
