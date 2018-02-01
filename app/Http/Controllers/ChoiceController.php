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
            'choices' => 'required|array|size:' . config('projects.required_projects'),
        ]);
        $choices = [];
        foreach ($request->choices as $choice) {
            $choices[key($choice)] = ['choice' => $choice[key($choice)]];
        }
        $request->user()->projects()->sync($choices);

        Mail::to($request->user())->queue(new ChoiceConfirmation($request->user()));

        return redirect()->route('thank_you');
    }
}
