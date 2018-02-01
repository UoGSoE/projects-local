<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        return redirect()->route('thank_you');
    }
}
