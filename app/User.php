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
        return $this->belongsToMany(Project::class, 'project_students', 'student_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_students', 'student_id', 'course_id');
    }

    public function scopeStudents($query)
    {
        return $query->where('is_staff', '=', false);
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
}
