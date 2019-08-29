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
        $user['ugrad_beng_active'] = $this->ugradActive('B.Eng');
        $user['ugrad_beng_allocated'] = $this->ugradAllocated('B.Eng');
        $user['ugrad_meng_active'] = $this->ugradActive('M.Eng');
        $user['ugrad_meng_allocated'] = $this->ugradAllocated('M.Eng');
        $user['ugrad_etc_active'] = $this->ugradActive('SIT/UESTC');
        $user['ugrad_etc_allocated'] = $this->ugradAllocated('SIT/UESTC');
        $user['pgrad_active'] = $this->pgradActive();
        $user['pgrad_allocated'] = $this->pgradAllocated();
        $user['second_ugrad_beng_active'] = $this->secondUgradActive('B.Eng');
        $user['second_ugrad_beng_allocated'] = $this->secondUgradAllocated('B.Eng');
        $user['second_ugrad_meng_active'] = $this->secondUgradActive('M.Eng');
        $user['second_ugrad_meng_allocated'] = $this->secondUgradAllocated('M.Eng');
        $user['second_ugrad_etc_active'] = $this->secondUgradActive('SIT/UESTC');
        $user['second_ugrad_etc_allocated'] = $this->secondUgradAllocated('SIT/UESTC');
        $user['second_pgrad_active'] = $this->secondPgradActive();
        $user['second_pgrad_allocated'] = $this->secondPgradAllocated();
        return $user;
    }

    public function ugradActive($type)
    {
        return $this->staffMember->staffProjects->filter(function ($project, $type) {
            return $project->isUndergrad() && $project->isType($type) && $project->isActive();
        })->count();
    }

    public function ugradInactive($type)
    {
        return $this->staffMember->staffProjects->filter(function ($project, $type) {
            return $project->isUndergrad() && $project->isType($type) && $project->isInactive();
        })->count();
    }

    public function ugradAllocated($type)
    {
        return $this->staffMember->staffProjects->filter(function ($project, $type) {
            return $project->isUndergrad() && $project->isType($type) && $project->isFullyAllocated();
        })->count();
    }

    public function secondUgradAllocated($type)
    {
        return $this->staffMember->secondSupervisorProjects->filter(function ($project, $type) {
            return $project->isUndergrad() && $project->isType($type) && $project->isFullyAllocated();
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

    public function secondUgradActive($type)
    {
        return $this->staffMember->secondSupervisorProjects->filter(function ($project, $type) {
            return $project->isUndergrad() && $project->isType($type) && $project->isActive();
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
