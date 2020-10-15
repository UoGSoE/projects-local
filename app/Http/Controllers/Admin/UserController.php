<?php

namespace App\Http\Controllers\Admin;

use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index($category = 'staff')
    {
        $users = User::ofType($category)
            ->with(['staffProjects.students', 'secondSupervisorProjects'])
            ->orderBy('surname')
            ->get()
            ->map(function ($user) {
                return $user->getProjectStats();
            });

        return view('admin.user.index', [
            'category' => $category,
            'users' => $users,
        ]);
    }

    public function show(User $user)
    {
        return view('admin.user.show', [
            'user' => $user,
        ]);
    }

    public function destroy(User $user)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), $this->getDeleteMessageFor($user)));
        $user->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Deleted']);
        }

        return redirect('/')->with('success', 'User deleted');
    }

    public function toggleAdmin(User $user)
    {
        if (\Auth::user()->id == $user->id) {
            return response()->json(['message' => 'Cannot change your own status'], 422);
        }
        $user->toggleAdmin();

        return response()->json(['status' => 'ok']);
    }

    protected function getDeleteMessageFor(User $user)
    {
        if ($user->isStudent()) {
            return "Deleted student {$user->matric}";
        }

        return "Deleted staffmember {$user->full_name}";
    }
}
