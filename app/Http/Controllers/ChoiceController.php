<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChoiceConfirmation;

class ChoiceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'choices' => 'required|array|size:' . config('projects.required_choices'),
        ]);
        $choices = [];
        foreach ($request->choices as $choice => $projectId) {
            $choices[$projectId] = ['choice' => $choice];
        }
        $request->user()->projects()->sync($choices);

        Mail::to($request->user())->queue(new ChoiceConfirmation($request->user()));

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Choices submitted'
            ]);
        }
        return redirect()->route('thank_you');
    }

    public function thankYou()
    {
        return 'Thank you';
    }
}
