<?php

namespace App;

use App\Mail\AcceptedOntoProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Project extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_placement' => 'boolean',
        'is_confidential' => 'boolean',
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

    public function secondSupervisor()
    {
        return $this->belongsTo(User::class, 'second_supervisor_id');
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

    public function hasSecondSupervisor()
    {
        return !is_null($this->second_supervisor_id);
    }

    public function doesntHaveAcceptedStudent($student)
    {
        return $this->students()->where('username', '=', $student->username)->count() == 0;
    }

    public static function createFromPlacementSheet(array $data)
    {
        $project = static::firstOrCreate(['title' => $data['title']], [
            'title' => $data['title'],
            'description' => $data['description'],
            'pre_req' => $data['prereq'],
            'category' => $data['category'],
            'is_active' => $data['active'] == 'y',
            'is_placement' => $data['placement'] == 'y',
            'is_confidential' => $data['confidential'] == 'y',
            'staff_id' => $data['staff']->id,
            'max_students' => $data['max_students'],
        ]);
        $project->courses()->sync([$data['course']->id]);
        $project->programmes()->sync([$data['programme']->id]);
        if ($project->doesntHaveAcceptedStudent($data['student'])) {
            $project->addAndAccept($data['student']);
        }
        return $project;
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

    public function getOwnerNameAttribute()
    {
        return $this->owner->full_name;
    }

    public function getCourseCodesAttribute()
    {
        return $this->courses->implode('code', ' ');
    }

    public function getProgrammeTitlesAttribute()
    {
        return $this->programmes->implode('title', ' ');
    }

    public function isInactive()
    {
        return !$this->is_active;
    }

    public function isActive()
    {
        return !$this->isInactive();
    }

    public function markActive()
    {
        $this->update(['is_active' => true]);
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
        Mail::to($student)->later(now()->addSeconds(rand(0, 300)), new AcceptedOntoProject($this));
    }

    public function addAndAccept(User $student)
    {
        $student->projects()->sync([$this->id => ['is_accepted' => false, 'choice' => 1]]);
        $this->accept($student);
    }

    public function unAccept(User $student)
    {
        $student->projects()->syncWithoutDetaching([$this->id => ['is_accepted' => false]]);
    }

    public function removeAllStudents()
    {
        $this->students->each->delete();
    }
}
