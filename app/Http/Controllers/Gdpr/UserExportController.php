<?php

namespace App\Http\Controllers\Gdpr;

use App\User;
use Illuminate\Http\Request;
use App\Http\Resources\Student;
use App\Http\Resources\StaffMember;
use App\Http\Controllers\Controller;

class UserExportController extends Controller
{
    public function show(User $user)
    {
        if ($user->isStudent()) {
            return new Student($user);
        }
        return new StaffMember($user);
    }
}
