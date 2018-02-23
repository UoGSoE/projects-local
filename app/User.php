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

    public function projects()
    {
        if ($this->is_staff) {
            return $this->hasMany(Project::class, 'staff_id');
        }
        return $this->belongsToMany(Project::class, 'project_students', 'student_id')->withPivot(['choice', 'is_accepted']);
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

    public function applicableProjects()
    {
        if (! $this->course_id) {
            return collect([]);
        }
        return $this->course->projects()->active()->get();
    }

    public function isntOnACourse()
    {
        return $this->course_id == null;
    }

    public function getFullNameAttribute()
    {
        return $this->surname . ' ' . $this->forenames;
    }

    public function isAccepted()
    {
        return $this->projects()->wherePivot('is_accepted', '=', true)->count() > 0;
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
        return ! $this->isStaff();
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
        $this->is_admin = ! $this->is_admin;
        $this->save();
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
}
