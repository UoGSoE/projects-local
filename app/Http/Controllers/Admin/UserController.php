<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index($category = 'staff')
    {
        return view('admin.user.index', [
            'category' => $category,
            'users' => User::ofType($category)->orderBy('surname')->get(),
        ]);
    }

    public function show(User $user)
    {
        return view('admin.user.show', [
            'user' => $user,
        ]);
    }

    public function toggleAdmin(User $user)
    {
        if (\Auth::user()->id == $user->id) {
            return response()->json(['message' => 'Cannot change your own status'], 422);
        }
        $user->toggleAdmin();
        return response()->json(['status' => 'ok']);
    }
}
