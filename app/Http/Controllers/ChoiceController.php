<?php

namespace App\Http\Controllers;

use App\Events\SomethingNoteworthyHappened;
use App\Mail\ChoiceConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ChoiceController extends Controller
{
    public function store(Request $request)
    {
        if ($request->user()->isAccepted()) {
            return redirect('/');
        }

        $request->validate([
            'choices' => 'required|array|size:' . config('projects.required_choices'),
            'research_area' => 'required',
        ]);

        $choices = [];
        foreach ($request->choices as $choice => $projectId) {
            $choices[$projectId] = ['choice' => $choice];
        }

        $request->user()->projects()->sync($choices);
        $request->user()->update(['research_area' => $request->research_area]);

        Mail::to($request->user())->queue(new ChoiceConfirmation($request->user()));

        $titles = $request->user()->projects->map(function ($project) {
            return $project->title;
        })->implode(', ');

        event(new SomethingNoteworthyHappened($request->user(), 'Applied for projects ' . $titles));

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Choices submitted',
            ]);
        }
        return redirect()->route('thank_you');
    }

    public function thankYou()
    {
        return view('student.thankyou');
    }
}
