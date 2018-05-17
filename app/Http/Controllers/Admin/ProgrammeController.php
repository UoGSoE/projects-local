<?php

namespace App\Http\Controllers\Admin;

use App\Programme;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class ProgrammeController extends Controller
{
    public function index()
    {
        return view('admin.programme.index', [
            'programmes' => Programme::with('projects.students', 'projects.courses', 'projects.owner')
                                ->orderBy('title')
                                ->withCount('projects')
                                ->get()
                                ->each
                                ->append('places_count', 'accepted_count'),
        ]);
    }

    public function create()
    {
        return view('admin.programme.create', [
            'programme' => new Programme,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|unique:programmes',
            'category' => 'required|in:undergrad,postgrad',
        ]);

        $programme = Programme::create($data);

        return redirect()->route('admin.programme.index')->with('success', 'Programme created');
    }

    public function edit($id)
    {
        return view('admin.programme.edit', [
            'programme' => Programme::findOrFail($id),
        ]);
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'title' => [
                'required',
                Rule::unique('programmes')->ignore($id),
            ],
            'category' => 'required|in:undergrad,postgrad',
        ]);

        Programme::findOrFail($id)->update($data);

        return redirect()->route('admin.programme.index')->with('success', 'Programme updated');
    }

    public function destroy($id, Request $request)
    {
        Programme::findOrFail($id)->delete();

        session()->flash('success', 'Programme deleted');

        if ($request->wantsJson()) {
            return response()->json(['status' => 'deleted']);
        }

        return redirect()->route('admin.programme.index')->with('success', 'Programme deleted');
    }
}