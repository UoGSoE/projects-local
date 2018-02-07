<?php

namespace App\Http\Controllers;

use App\Programme;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgrammeController extends Controller
{
    public function index()
    {
        return view('admin.programme.index', [
            'programmes' => Programme::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|unique:programmes',
        ]);

        $programme = Programme::create($data);

        return redirect()->route('admin.programme.index')->with('success', 'Programme created');
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'title' => [
                'required',
                Rule::unique('programmes')->ignore($id),
            ],
        ]);

        Programme::findOrFail($id)->update($data);

        return redirect()->route('admin.programme.index')->with('success', 'Programme updated');
    }

    public function destroy($id)
    {
        Programme::findOrFail($id)->delete();

        return redirect()->route('admin.programme.index')->with('success', 'Programme deleted');
    }
}
