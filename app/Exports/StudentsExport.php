<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class StudentsExport implements FromCollection
{
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::ofType($this->type)
            ->with('projects.programmes', 'course')
            ->orderBy('surname')
            ->get()
            ->map(function ($student, $key) {
                $acceptedProject = $student->projects()->wherePivot('is_accepted', true)->first();
                $choice1 = $student->projects->where('pivot.choice', 1)->first();
                $choice2 = $student->projects->where('pivot.choice', 2)->first();
                $choice3 = $student->projects->where('pivot.choice', 3)->first();
                $choice4 = $student->projects->where('pivot.choice', 4)->first();
                $choice5 = $student->projects->where('pivot.choice', 5)->first();

                return [
                    'matric' => $student->matric,
                    'surname' => $student->surname,
                    'forenames' => $student->forenames,
                    'course' => $student->course->code,
                    'accepted_project' => optional($acceptedProject)->title,
                    'project_programmes' => optional($acceptedProject)->programme_titles,
                    'project_supervisor' => optional($acceptedProject)->owner_name,
                    'project_second_supervisor' => optional($acceptedProject)->second_supervisor_name,
                    'choice_1' => optional($choice1)->id,
                    'choice_1_title' => optional($choice1)->title,
                    'choice_2' => optional($choice2)->id,
                    'choice_2_title' => optional($choice2)->title,
                    'choice_3' => optional($choice3)->id,
                    'choice_3_title' => optional($choice3)->title,
                    'choice_4' => optional($choice4)->id,
                    'choice_4_title' => optional($choice4)->title,
                    'choice_5' => optional($choice5)->id,
                    'choice_5_title' => optional($choice5)->title,
                ];
            })
            ->prepend([
                'Matric',
                'Surname',
                'Forenames',
                'Course',
                'Accepted Project',
                'Project Programmes',
                'Project Supervisor',
                'Project Second Supervisor',
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
            ]);
    }
}
