<?php

namespace App\Http\Controllers;

use App\Course;
use App\Events\SomethingNoteworthyHappened;
use App\Programme;
use App\Project;
use App\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function show($id)
    {
        $project = Project::with('owner', 'programmes', 'courses', 'students')->withCount([
            'students',
            'students as accepted_students_count' => function ($query) {
                return $query->where('is_accepted', '=', true);
            },
        ])->findOrFail($id);
        $project->staff_can_accept = $project->allowsStaffToAcceptStudents();

        $this->authorize('view', $project);

        $students = User::students()->orderBy('surname')->get();

        return view('project.show', ['project' => $project, 'students' => $students]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'type' => 'required|in:undergrad,postgrad',
        ]);

        return view('project.create', [
            'project' => new Project([
                'category' => $request->type,
                'max_students' => 1,
                'is_active' => true,
                'staff_id' => auth()->id(),
            ]),
            'programmes' => Programme::where('category', '=', $request->type)->orderBy('title')->get(),
            'courses' => Course::where('category', '=', $request->type)->orderBy('title')->get(),
            'staff' => User::staff()->orderBy('surname')->get(),
        ]);
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
            'staff_id' => 'nullable|integer',
            'is_active' => 'boolean',
            'is_placement' => 'boolean',
            'is_confidential' => 'boolean',
        ]);

        $user = $request->user();
        if ($user->isAdmin() and $request->filled('staff_id')) {
            $user = User::findOrFail($request->staff_id);
        }
        $project = $user->projects()->create(collect($data)->except(['courses', 'programmes'])->toArray());
        $project->programmes()->sync($request->programmes);
        $project->courses()->sync($request->courses);

        event(new SomethingNoteworthyHappened(auth()->user(), "Created project {$project->title}"));

        return redirect(route('project.show', $project->id));
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        return view('project.edit', [
            'project' => $project,
            'programmes' => Programme::where('category', '=', $project->category)->orderBy('title')->get(),
            'courses' => Course::where('category', '=', $project->category)->orderBy('title')->get(),
            'staff' => User::staff()->orderBy('surname')->get(),
        ]);
    }

    public function update($id, Request $request)
    {
        $validationRules = [
            'title' => 'required',
            'category' => 'required',
            'pre_req' => 'nullable',
            'description' => 'required',
            'max_students' => 'required|integer',
            'courses' => 'required|array|min:1',
            'programmes' => 'required|array|min:1',
            'is_active' => 'boolean',
            'is_placement' => 'boolean',
            'is_confidential' => 'boolean',
        ];
        if ($request->user()->isAdmin() and $request->filled('staff_id')) {
            $validationRules['staff_id'] = 'required|integer|exists:users,id';
        }

        $data = $request->validate($validationRules);

        $project = Project::findOrFail($id);
        $this->authorize('update', $project);

        $project->update(collect($data)->except(['courses', 'programmes'])->toArray());
        $project->programmes()->sync($request->programmes);
        $project->courses()->sync($request->courses);

        event(new SomethingNoteworthyHappened(auth()->user(), "Updated project {$project->title}"));

        return redirect(route('project.show', $project->id))->with('success', 'Project Updated');
    }

    public function destroy($id, Request $request)
    {
        $project = Project::findOrFail($id);
        $this->authorize('delete', $project);

        $project->delete();

        event(new SomethingNoteworthyHappened(auth()->user(), "Deleted project {$project->title}"));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'deleted']);
        }

        return redirect()->route('home')->with('success', 'Project Deleted');
    }
}
