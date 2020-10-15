<?php

namespace App\Http\Controllers\Admin;

use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    public function store($id)
    {
        session(['original_id' => auth()->id()]);
        $user = User::findOrFail($id);

        event(new SomethingNoteworthyHappened(auth()->user(), "Started impersonating {$user->full_name}"));

        auth()->login($user);

        return redirect(route('home'));
    }

    public function destroy()
    {
        $admin = User::findOrFail(session('original_id'));
        event(new SomethingNoteworthyHappened($admin, 'Stopped impersonating '.auth()->user()->full_name));

        auth()->login($admin);

        session()->forget('original_id');

        return redirect(route('home'));
    }
}
