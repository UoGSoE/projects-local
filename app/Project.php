<?php

namespace App;

use App\Mail\AcceptedOntoProject;
use Illuminate\Support\Facades\Mail;
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

    public function owner()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function accept(User $student)
    {
        $student->projects()->sync([$this->id => ['is_accepted' => true]]);
        Mail::to($student)->queue(new AcceptedOntoProject($this));
    }
}
