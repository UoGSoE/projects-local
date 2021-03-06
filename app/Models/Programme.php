<?php

namespace App\Models;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
