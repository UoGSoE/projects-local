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

        collect($request->active)->reject(function ($obj) {
            return $obj['id'] == 0;
        })->each(function ($obj) use ($request) {
            $project = Project::findOrFail($obj['id']);
            if ($project->is_active != $obj['is_active']) {
                event(new SomethingNoteworthyHappened(
                    $request->user(),
                    "Set project {$project->title} as " . ($obj['is_active'] ? 'active' : 'inactive')
                ));
                $project->update(['is_active' => $obj['is_active']]);
            }
        });

        collect($request->delete)->each(function ($projectId) use ($request) {
            $project = Project::findorFail($projectId);
            event(new SomethingNoteworthyHappened(
                $request->user(),
                "Deleted project {$project->title}"
            ));
            $project->delete();
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
