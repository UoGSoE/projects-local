<?php

namespace App\Http\Controllers;

use App\Events\SomethingNoteworthyHappened;
use App\Mail\ChoiceConfirmation;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ChoiceController extends Controller
{
    public function store(Request $request)
    {
        if ($request->user()->isAccepted()) {
            return redirect('/');
        }

        $data = $request->validate([
            'choices' => 'required|array|size:'.config('projects.required_choices'),
            'research_area' => 'required',
        ]);

        $choices = [];
        $projectList = collect([]);
        foreach ($request->choices as $choice => $projectId) {
            $choices[$projectId] = ['choice' => $choice];
            $projectList->push(Project::find($projectId));
        }

        // check that they aren't trying to choose more than three projects with the same supervisor
        $nooneHasMoreThanThreeProjects = $projectList->pluck('staff_id')
                ->countBy()
                ->every(function ($total, $supervisorId) {
                    return $total < 4;
                });
        if (! $nooneHasMoreThanThreeProjects) {
            throw ValidationException::withMessages([
                'supervisor' => 'You cannot choose more than three projects with the same supervisor',
            ]);
        }
        $request->user()->projects()->sync($choices);
        $request->user()->update(['research_area' => $data['research_area']]);

        Mail::to($request->user())->queue(new ChoiceConfirmation($request->user()));

        $titles = $request->user()->projects->map(function ($project) {
            return $project->title;
        })->implode(', ');

        event(new SomethingNoteworthyHappened($request->user(), 'Applied for projects '.$titles));

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Choices submitted',
            ]);
        }

        return redirect()->route('thank_you');
    }

    public function thankYou(): View
    {
        return view('student.thankyou');
    }
}
