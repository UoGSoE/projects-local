<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('student.profile.edit');
    }

    public function update(Request $request)
    {
        $request->user()->update(['profile' => $request->profile]);

        return redirect('/')->with('success', 'Profile updated');
    }
}