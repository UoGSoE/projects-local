<?php

namespace App;

use App\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Programme extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_programmes');
    }

    public function getPlacesCountAttribute()
    {
        return $this->projects->sum('max_students');
    }

    public function getAcceptedCountAttribute()
    {
        return $this->projects->sum(function ($project) {
            return $project->students->sum(function ($student) {
                return intval($student->pivot->is_accepted);
            });
        });
    }
}
