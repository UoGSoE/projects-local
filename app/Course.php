<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $guarded = [];

    public function students()
    {
        return $this->hasMany(User::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_courses');
    }

    public function scopeUndergrad($query)
    {
        return $query->where('category', '=', 'undergrad');
    }

    public function scopePostgrad($query)
    {
        return $query->where('category', '=', 'postgrad');
    }

    public function removeAllStudents()
    {
        $this->students->each->delete();
    }
}
