<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function index(string $type)
    {
        Validator::make(['type' => $type], [
            'type' => 'required|in:undergrad,postgrad',
        ])->validate();

        $activeProjects = Project::active()->with(['students', 'owner'])->where('category', '=', $type)->get();

        $studentList = $activeProjects->map(function ($project) {
            return $project->students->filter(fn ($student) => $student->pivot->is_accepted)->map(function ($student) use ($project) {
                return [
                    'username' => $student->username,
                    'email' => $student->email,
                    'surname' => $student->surname,
                    'forenames' => $student->forenames,
                    'supervisor' => [
                        'username' => $project->owner->username,
                        'email' => $project->owner->email,
                        'surname' => $project->owner->surname,
                        'forenames' => $project->owner->forenames,
                    ],
                ];
            });
        })->flatten(1);

        return response()->json([
            'data' => $studentList
        ]);
    }
}