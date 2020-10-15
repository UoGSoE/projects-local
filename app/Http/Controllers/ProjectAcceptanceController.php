<?php

namespace App\Http\Controllers;

use App\Events\SomethingNoteworthyHappened;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectAcceptanceController extends Controller
{
    public function store($id, Request $request)
    {
        $request->validate([
            'students' => 'present|array',
        ]);

        $project = Project::findOrFail($id);

        $this->authorize('accept-students', $project);

        if ($request->user()->isAdmin()) {
            $this->unacceptStudentsWhoHaveBeenUnaccepted($project, $request->students);
        }

        $students = User::students()->findMany($request->students);
        $students->each(function ($student) use ($project) {
            $this->authorize('accept-onto-project', [$student, $project]);
            $project->accept($student);
        });
        $matrics = $students->map(function ($student) {
            return $student->matric;
        })->implode(', ');

        event(new SomethingNoteworthyHappened(
            $request->user(),
            "Accepted students {$matrics} onto project {$project->title}"
        ));

        return redirect(route('project.show', $project->id))->with('success', 'Students Accepted');
    }

    public function unacceptStudentsWhoHaveBeenUnaccepted(Project $project, array $studentIds)
    {
        $students = $project->students()->whereNotIn('student_id', $studentIds);
        $students->each(function ($student) use ($project) {
            $project->unAccept($student);
        });
    }
}
