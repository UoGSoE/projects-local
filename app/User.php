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

    public function projects()
    {
        if ($this->is_staff) {
            return $this->hasMany(Project::class, 'staff_id');
        }
        return $this->belongsToMany(Project::class, 'project_students', 'student_id');
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
}
