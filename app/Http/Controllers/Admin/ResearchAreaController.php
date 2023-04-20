<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Models\ResearchArea;
use Illuminate\Http\Request;

class ResearchAreaController extends Controller
{
    public function index(): View
    {
        return view('admin.researcharea.index', [
            'areas' => ResearchArea::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required',
        ]);

        $area = ResearchArea::create(['title' => $request->title]);

        event(new SomethingNoteworthyHappened($request->user(), "Created new research area {$request->title}"));

        return response()->json([
            'area' => $area->toJson(),
        ], 201);
    }

    public function update(ResearchArea $area, Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required',
        ]);

        $area->update(['title' => $request->title]);

        event(new SomethingNoteworthyHappened($request->user(), "Updated research area {$request->title}"));

        return response()->json([
            'message' => 'Updated',
            'area' => $area->toJson(),
        ]);
    }

    public function destroy(ResearchArea $area): JsonResponse
    {
        event(new SomethingNoteworthyHappened(auth()->user(), "Deleted research area {$area->title}"));

        $area->delete();

        return response()->json([
            'message' => 'Deleted',
        ]);
    }
}
