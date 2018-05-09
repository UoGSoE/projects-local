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
        'is_placement' => 'boolean',
        'is_confidential' => 'boolean',
    ];

    // protected $appends = [
    //     'course_codes',
    //     'owner_name',
    // ];

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

    public function studentsAsJson()
    {
        return $this->students->map(function ($student) {
            $base = $student->toArray();
            $base['choice'] = intval($student->pivot->choice);
            $base['is_accepted'] = (boolean) $student->pivot->is_accepted;
            return $base;
        })->toJson();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function secondSupervisor()
    {
        return $this->belongsTo(User::class, 'second_supervisor_id');
    }

    public function hasSecondSupervisor()
    {
        return ! is_null($this->second_supervisor_id);
    }

    public function getOwnerNameAttribute()
    {
        return $this->owner->full_name;
    }

    public function scopeUndergrad($query)
    {
        return $query->where('category', '=', 'undergrad');
    }

    public function scopePostgrad($query)
    {
        return $query->where('category', '=', 'postgrad');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', '=', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', '=', false);
    }

    public function getCourseCodesAttribute()
    {
        return $this->courses->implode('code', ' ');
    }

    public function isInactive()
    {
        return ! $this->is_active;
    }

    public function isActive()
    {
        return ! $this->isInactive();
    }

    public function isConfidential()
    {
        return $this->is_confidential;
    }

    public function isPlacement()
    {
        return $this->is_placement;
    }

    public function isUndergrad()
    {
        return $this->category === 'undergrad';
    }

    public function isPostgrad()
    {
        return $this->category === 'postgrad';
    }

    public function accept(User $student)
    {
        if ($student->isAcceptedOn($this)) {
            return;
        }

        $student->projects()->sync([$this->id => ['is_accepted' => true]]);
        Mail::to($student)->queue(new AcceptedOntoProject($this));
    }

    public function addAndAccept(User $student)
    {
        $student->projects()->sync([$this->id => ['is_accepted' => false, 'choice' => 1]]);
        $this->accept($student);
    }

    public function deleteStudents()
    {
        $this->students->each->delete();
    }
}
