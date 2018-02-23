<?php

namespace App;

use App\Mail\AcceptedOntoProject;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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
        return $this->belongsToMany(User::class, 'project_students', 'project_id', 'student_id')
                    ->withPivot('is_accepted', 'choice');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function scopeUndergrad($query)
    {
        return $query->where('category', '=', 'undergrad');
    }

    public function scopePostgrad($query)
    {
        return $query->where('category', '=', 'postgrad');
    }

    public function isInactive()
    {
        return ! $this->is_active;
    }

    public function isActive()
    {
        return ! $this->isInactive();
    }

    public function accept(User $student)
    {
        $student->projects()->sync([$this->id => ['is_accepted' => true]]);
        Mail::to($student)->queue(new AcceptedOntoProject($this));
    }

    public function deleteStudents()
    {
        $this->students->each->delete();
    }
}
