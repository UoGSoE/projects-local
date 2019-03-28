<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\SomethingNoteworthyHappened;

class UserController extends Controller
{
    public function show(Request $request)
    {
        $request->validate([
            'guid' => 'required',
        ]);

        $existingUser = User::where('username', '=', $request->guid)->first();
        if ($existingUser) {
            return response()->json([
                'data' => [],
                'message' => 'Already Exists',
            ], 422);
        }

        $ldapUser = \Ldap::findUser($request->guid);

        if (!$ldapUser) {
            return response()->json([
                'data' => [],
                'message' => 'Not Found'
            ], 404);
        }

        return response()->json([
            'data' => [
                'user' => $ldapUser->toArray(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'guid' => 'required',
        ]);

        $existingUser = User::where('username', '=', $request->guid)->first();
        if ($existingUser) {
            return response()->json([
                'data' => [],
                'message' => 'Duplicate',
            ], 422);
        }

        $ldapUser = \Ldap::findUser($request->guid);

        if (!$ldapUser) {
            return response()->json([
                'data' => [],
                'message' => 'Not Found'
            ], 404);
        }

        $user = User::create([
            'username' => $ldapUser->username,
            'email' => $ldapUser->email,
            'surname' => $ldapUser->surname,
            'forenames' => $ldapUser->forenames,
            'is_staff' => !$this->looksLikeMatric($ldapUser->username),
            'password' => bcrypt(Str::random(64)),
        ]);

        event(new SomethingNoteworthyHappened(auth()->user(), "Created user {$user->full_name}"));

        return response()->json([
            'data' => $user->toArray(),
            'message' => 'Saved'
        ]);
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        User::findOrFail($id)->update(['email' => $request->email]);

        return response()->json([
            'message' => 'Updated',
        ]);
    }

    protected function looksLikeMatric($username)
    {
        if (preg_match('/^[0-9]/', $username) === 1) {
            return true;
        }
        return false;
    }
}
