<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ImpersonationController extends Controller
{
    public function store($id)
    {
        session(['original_id' => auth()->id()]);

        auth()->loginUsingId($id);

        return redirect(route('home'));
    }

    public function destroy()
    {
        auth()->loginUsingId(session('original_id'));

        session()->forget('original_id');

        return redirect(route('home'));
    }
}
