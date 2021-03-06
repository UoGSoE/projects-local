<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        if (! $ldapUser) {
            return response()->json([
                'data' => [],
                'message' => 'Not Found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'user' => $ldapUser->toArray(),
            ],
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

        if (! $ldapUser) {
            return response()->json([
                'data' => [],
                'message' => 'Not Found',
            ], 404);
        }

        $user = User::create([
            'username' => strtolower($ldapUser->username),
            'email' => strtolower($ldapUser->email),
            'surname' => $ldapUser->surname,
            'forenames' => $ldapUser->forenames,
            'is_staff' => ! $this->looksLikeMatric($ldapUser->username),
            'password' => bcrypt(Str::random(64)),
        ]);

        if ($request->filled('course') and ($this->looksLikeMatric($ldapUser->username))) {
            $course = Course::where('code', '=', strtoupper($request->course))->firstOrFail();
            $user->course()->associate($course);
            $user->save();
        }

        event(new SomethingNoteworthyHappened(auth()->user(), "Created user {$user->full_name}"));

        return response()->json([
            'data' => $user->toArray(),
            'message' => 'Saved',
        ]);
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        User::findOrFail($id)->update(['email' => strtolower($request->email)]);

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
