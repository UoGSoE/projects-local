<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the project.
     *
     * @return mixed
     */
    public function view(User $user, Project $project): bool
    {
        return $user->id == $project->staff_id;
    }

    /**
     * Determine whether the user can create projects.
     *
     * @return mixed
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the project.
     *
     * @return mixed
     */
    public function update(User $user, Project $project): bool
    {
        return $user->id == $project->staff_id;
    }

    /**
     * Determine whether the user can delete the project.
     *
     * @return mixed
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->id == $project->staff_id;
    }
}
