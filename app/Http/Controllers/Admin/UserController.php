<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.user.index', [
            'users' => User::orderBy('surname')->get(),
        ]);
    }

    public function toggleAdmin(User $user)
    {
        $user->toggleAdmin();
        return response()->json(['status' => 'ok']);
    }
}
