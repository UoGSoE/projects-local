<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = [];

    public function programmes()
    {
        return $this->belongsToMany(Programme::class, 'project_programmes');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'project_courses');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'project_students', 'id', 'student_id')
                    ->withPivot('is_accepted', 'choice');
    }

    public function accept(User $student)
    {
        $student->projects()->sync([$this->id => ['is_accepted' => true]]);
    }
}
