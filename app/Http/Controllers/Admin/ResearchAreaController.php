<?php

namespace App\Http\Controllers\Admin;

use App\ResearchArea;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ResearchAreaController extends Controller
{
    public function index()
    {
        return view('admin.researcharea.index', [
            'areas' => ResearchArea::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $area = ResearchArea::create(['title' => $request->title]);

        return response()->json([
            'area' => $area->toJson(),
        ], 201);
    }

    public function update(ResearchArea $area, Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $area->update(['title' => $request->title]);

        return response()->json([
            'message' => 'Updated',
            'area' => $area->toJson(),
        ]);
    }

    public function destroy(ResearchArea $area)
    {
        $area->delete();

        return response()->json([
            'message' => 'Deleted',
        ]);
    }
}
