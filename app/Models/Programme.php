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

    public function students()
    {
        return $this->hasMany(User::class);
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

    public function transferProjectsToProgramme(self $programme): void
    {
        $programme->projects()->syncWithoutDetaching($this->projects->pluck('id'));
        $this->projects()->detach();
    }

    public function transferStudentsToProgramme(self $programme): void
    {
        $this->students->each->update(['programme_id' => $programme->id]);
    }
}
