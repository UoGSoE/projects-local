<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_staff' => 'boolean',
        'is_admin' => 'boolean',
    ];

    protected $appends = [
        'full_name',
    ];

    public function projects()
    {
        if ($this->is_staff) {
            return $this->hasMany(Project::class, 'staff_id');
        }
        return $this->belongsToMany(Project::class, 'project_students', 'student_id')->withPivot(['choice', 'is_accepted']);
    }

    /**
     * @todo this is here to make eager-loading work on the admin controller @index method
     * really needs to refactor the code everywhere to use different staff/student projects relations
     */
    public function staffProjects()
    {
        return $this->hasMany(Project::class, 'staff_id');
    }

    public function undergradProjects()
    {
        return $this->projects()->where('category', '=', 'undergrad');
    }

    public function postgradProjects()
    {
        return $this->projects()->where('category', '=', 'undergrad');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeStaff($query)
    {
        return $query->where('is_staff', '=', true);
    }

    public function scopeStudents($query)
    {
        return $query->where('is_staff', '=', false);
    }

    public function scopeOfType($query, $type)
    {
        if ($type === 'staff') {
            return $this->scopeStaff($query);
        }

        // this is because we don't know if a _student_ is under/post-grad - only the course
        // or project they are on lets us know
        return $this->scopeStudents($query)->whereHas('course', function ($query) use ($type) {
            $query->where('category', '=', $type);
        })->orWhereHas('projects', function ($query) use ($type) {
            $query->where('category', '=', $type);
        });
    }

    public function isTooLate()
    {
        // if admin is impersonating a student - ignore the rules they asked for :'-/
        if ($this->isImpersonating()) {
            return false;
        }
        return $this->course->application_deadline->lt(now());
    }

    public function isImpersonating()
    {
        if (session('original_id')) {
            return true;
        }
        return false;
    }

    public function applicableProjects()
    {
        if (!$this->course_id) {
            return collect([]);
        }
        return $this->course->projects()->with('owner', 'programmes')->active()->get();
    }

    public function applicableProgrammes()
    {
        if (!$this->course_id) {
            return collect([]);
        }
        return Programme::where('category', '=', $this->course->category)->orderBy('title')->get();
    }

    public function isntOnACourse()
    {
        return $this->course_id == null;
    }

    public function getFullNameAttribute()
    {
        return $this->surname . ', ' . $this->forenames;
    }

    public function isAccepted()
    {
        return $this->projects()->wherePivot('is_accepted', '=', true)->count() > 0;
    }

    public function isAcceptedOn($project)
    {
        return $this->projects()->findOrFail($project->id)->pivot->is_accepted;
    }

    public function isntAcceptedOn($project)
    {
        return !$this->isAcceptedOn($project);
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }

    public function isStaff()
    {
        return $this->is_staff;
    }

    public function isStudent()
    {
        return !$this->isStaff();
    }

    public function isFirstChoice(Project $project)
    {
        if ($this->projects()->where('project_id', '=', $project->id)->wherePivot('choice', '=', 1)->first()) {
            return true;
        }
        return false;
    }

    public function toggleAdmin()
    {
        $this->update(['is_admin' => !$this->is_admin]);
    }

    public function makeAdmin()
    {
        $this->update(['is_admin' => true]);
    }

    public function getType()
    {
        if ($this->isAdmin()) {
            return 'Admin';
        }
        if ($this->isStaff()) {
            return 'Staff';
        }
        return 'Student';
    }

    public function getMatricAttribute()
    {
        return substr($this->username, 0, 7);
    }

    public function getShowUrlAttribute()
    {
        return route('admin.user.show', $this->id);
    }

    /**
     * Used to json-ify all the extra columns admins need to see in the staff list :'-/
     *
     * @return void
     */
    public function forAdminIndex()
    {
        $user = $this->toArray();
        $user['ugrad_active'] = $this->ugrad_active;
        $user['ugrad_inactive'] = $this->ugrad_inactive;
        $user['pgrad_active'] = $this->pgrad_active;
        $user['pgrad_inactive'] = $this->pgrad_inactive;
        return $user;
    }

    public function getUgradActiveAttribute()
    {
        return $this->staffProjects->filter(function ($project) {
            return $project->isUndergrad() && $project->isActive();
        })->count();
    }

    public function getUgradInactiveAttribute()
    {
        return $this->staffProjects->filter(function ($project) {
            return $project->isUndergrad() && $project->isInactive();
        })->count();
    }

    public function getPgradActiveAttribute()
    {
        return $this->staffProjects->filter(function ($project) {
            return $project->isPostgrad() && $project->isActive();
        })->count();
    }

    public function getPgradInactiveAttribute()
    {
        return $this->staffProjects->filter(function ($project) {
            return $project->isPostgrad() && $project->isInactive();
        })->count();
    }

    public function anonymise()
    {
        $anonInfo = "ANON{$this->id}";
        $this->update([
            'username' => $anonInfo,
            'surname' => $anonInfo,
            'forenames' => $anonInfo,
            'email' => $anonInfo . '@glasgow.ac.uk',
        ]);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'isAdmin' => $this->isAdmin(),
            'surname' => $this->surname,
            'forenames' => $this->forenames,
            'type' => $this->getType(),
            'matric' => $this->matric,
        ];
    }
}
