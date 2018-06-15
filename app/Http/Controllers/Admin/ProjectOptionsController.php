<?php

namespace App\Http\Controllers\Admin;

use App\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\SomethingNoteworthyHappened;

class ProjectOptionsController extends Controller
{
    public function index($category)
    {
        $projects = Project::where('category', '=', $category)
            ->orderBy('title')
            ->with(['owner', 'secondSupervisor', 'courses'])
            ->get();

        $projects->each->append('course_codes');

        return view('admin.project.options.index', [
            'category' => $category,
            'projects' => $projects,
        ]);
    }

    public function update($category, Request $request)
    {
        $request->validate([
            'active' => 'array',
            'delete' => 'array',
        ]);
        event(new SomethingNoteworthyHappened($request->user(), 'Bulk updated project options'));

        // grab the model event dispacher as we might disable it do avoid spamming the activity log
        $dispacher = Project::getEventDispatcher();

        collect($request->active)->reject(function ($obj) {
            return $obj['id'] == 0;
        })->each(function ($obj) use ($request) {
            $project = Project::findOrFail($obj['id']);
            $project->unsetEventDispatcher();
            if ($project->is_active != $obj['is_active']) {
                event(new SomethingNoteworthyHappened(
                    $request->user(),
                    "Set project {$project->title} as " . ($obj['is_active'] ? 'active' : 'inactive')
                ));
                $project->update(['is_active' => $obj['is_active']]);
            }
        });

        // set the event dispacher back in case it was disabled by the is_active stuff above
        Project::setEventDispatcher($dispacher);

        collect($request->delete)->each(function ($projectId) {
            Project::findorFail($projectId)->delete();
        });

        session()->flash('success', 'Updated');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "Updated",
            ]);
        }

        return redirect()->route('admin.project.index', ['category' => $category]);
    }
}
