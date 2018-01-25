<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function show($id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('view', $project);

        return view('project.show', ['project' => $project]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'category' => 'required',
            'pre_req' => 'nullable',
            'description' => 'required',
            'max_students' => 'required|integer',
            'courses' => 'required|array|min:1',
            'programmes' => 'required|array|min:1',
        ]);

        $project = $request->user()->projects()->create(collect($data)->except(['courses', 'programmes'])->toArray());
        $project->programmes()->sync($request->programmes);
        $project->courses()->sync($request->courses);

        return redirect(route('project.show', $project->id));
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'category' => 'required',
            'pre_req' => 'nullable',
            'description' => 'required',
            'max_students' => 'required|integer',
            'courses' => 'required|array|min:1',
            'programmes' => 'required|array|min:1',
        ]);

        $project = Project::findOrFail($id);
        $this->authorize('update', $project);

        $project->update(collect($data)->except(['courses', 'programmes'])->toArray());
        $project->programmes()->sync($request->programmes);
        $project->courses()->sync($request->courses);

        return redirect(route('project.show', $project->id))->with('success', 'Project Updated');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('delete', $project);

        $project->delete();

        return redirect(route('home'))->with('success', 'Project Deleted');
    }
}
