<?php

namespace App\Exports;

use Illuminate\Support\Collection;
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
    public function collection(): Collection
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
                    'plan_code' => $student->programme?->plan_code,
                    'accepted_project' => $acceptedProject?->title,
                    'project_programmes' => $acceptedProject?->programme_titles,
                    'project_supervisor' => $acceptedProject?->owner_name,
                    'project_second_supervisor' => $acceptedProject?->second_supervisor_name,
                    'choice_1' => $choice1?->id,
                    'choice_1_title' => $choice1?->title,
                    'choice_2' => $choice2?->id,
                    'choice_2_title' => $choice2?->title,
                    'choice_3' => $choice3?->id,
                    'choice_3_title' => $choice3?->title,
                    'choice_4' => $choice4?->id,
                    'choice_4_title' => $choice4?->title,
                    'choice_5' => $choice5?->id,
                    'choice_5_title' => $choice5?->title,
                ];
            })
            ->prepend([
                'Matric',
                'Surname',
                'Forenames',
                'Plan Code',
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
