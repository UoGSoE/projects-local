<?php

namespace App;

class ProjectStats
{
    protected $staffMember;

    public function __construct(User $staffMember)
    {
        $this->staffMember = $staffMember;
    }

    public function get()
    {
        return $this->forAdminIndex();
    }

    /**
     * Used to json-ify all the extra columns admins need to see in the staff list :'-/
     *
     * @return void
     */
    public function forAdminIndex()
    {
        $user = $this->staffMember->toArray();
        $user['ugrad_active'] = $this->ugradActive();
        $user['ugrad_allocated'] = $this->ugradAllocated();
        $user['pgrad_active'] = $this->pgradActive();
        $user['pgrad_allocated'] = $this->pgradAllocated();
        $user['2nd_ugrad_active'] = $this->secondUgradActive();
        $user['2nd_ugrad_allocated'] = $this->secondUgradAllocated();
        $user['2nd_pgrad_active'] = $this->secondPgradActive();
        $user['2nd_pgrad_allocated'] = $this->secondPgradAllocated();
        return $user;
    }

    public function ugradActive()
    {
        return $this->staffMember->staffProjects->filter(function ($project) {
            return $project->isUndergrad() && $project->isActive();
        })->count();
    }

    public function ugradInactive()
    {
        return $this->staffMember->staffProjects->filter(function ($project) {
            return $project->isUndergrad() && $project->isInactive();
        })->count();
    }

    public function ugradAllocated()
    {
        return $this->staffMember->staffProjects->filter(function ($project) {
            return $project->isUndergrad() && $project->isFullyAllocated();
        })->count();
    }

    public function secondUgradAllocated()
    {
        return $this->staffMember->secondSupervisorProjects->filter(function ($project) {
            return $project->isUndergrad() && $project->isFullyAllocated();
        })->count();
    }

    public function pgradAllocated()
    {
        return $this->staffMember->staffProjects->filter(function ($project) {
            return $project->isPostgrad() && $project->isFullyAllocated();
        })->count();
    }

    public function secondPgradAllocated()
    {
        return $this->staffMember->secondSupervisorProjects->filter(function ($project) {
            return $project->isPostgrad() && $project->isFullyAllocated();
        })->count();
    }

    public function pgradActive()
    {
        return $this->staffMember->staffProjects->filter(function ($project) {
            return $project->isPostgrad() && $project->isActive();
        })->count();
    }

    public function pgradInactive()
    {
        return $this->staffMember->staffProjects->filter(function ($project) {
            return $project->isPostgrad() && $project->isInactive();
        })->count();
    }

    public function secondUgradActive()
    {
        return $this->staffMember->secondSupervisorProjects->filter(function ($project) {
            return $project->isUndergrad() && $project->isActive();
        })->count();
    }

    public function secondUgradInactive()
    {
        return $this->staffMember->secondSupervisorProjects->filter(function ($project) {
            return $project->isUndergrad() && $project->isInactive();
        })->count();
    }

    public function secondPgradActive()
    {
        return $this->staffMember->secondSupervisorProjects->filter(function ($project) {
            return $project->isPostgrad() && $project->isActive();
        })->count();
    }

    public function secondPgradInactive()
    {
        return $this->staffMember->secondSupervisorProjects->filter(function ($project) {
            return $project->isPostgrad() && $project->isInactive();
        })->count();
    }
}
