<?php

namespace App\Http\Controllers\Gdpr;

use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Http\Resources\StaffMember;
use App\Http\Resources\Student;
use App\User;
use Illuminate\Http\Request;

class UserExportController extends Controller
{
    public function show(User $user)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), "Exported GDPR data for {$user->username}"));

        if ($user->isStudent()) {
            return new Student($user);
        }

        return new StaffMember($user);
    }
}
