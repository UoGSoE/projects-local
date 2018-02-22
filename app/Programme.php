<?php

namespace App;

use App\Project;
use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    protected $guarded = [];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_programmes');
    }
}
