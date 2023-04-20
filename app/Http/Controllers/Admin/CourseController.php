<?php

namespace App\Http\Controllers\Admin;

use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.course.index', [
            'courses' => Course::orderBy('title')->withCount(['projects', 'students'])->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.course.create', [
            'course' => new Course(['application_deadline' => now()->addMonths(3)]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required',
            'code' => 'required|unique:courses',
            'category' => 'required|in:undergrad,postgrad',
            'application_deadline' => 'required|date_format:d/m/Y',
            'allow_staff_accept' => 'required|boolean',
        ]);
        $data['application_deadline'] = Carbon::createFromFormat(
            'd/m/Y',
            $data['application_deadline']
        )->hour(23)->minute(59);

        Course::create($data);

        event(new SomethingNoteworthyHappened(auth()->user(), "Created course {$data['code']}"));

        return redirect()->route('admin.course.index')->with('success', 'Course Created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course): View
    {
        return view('admin.course.show', [
            'course' => $course,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course): View
    {
        return view('admin.course.edit', [
            'course' => $course,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required',
            'code' => ['required', Rule::unique('courses')->ignore($course->id)],
            'category' => 'required|in:undergrad,postgrad',
            'application_deadline' => 'required|date_format:d/m/Y',
            'allow_staff_accept' => 'required|boolean',
        ]);
        $data['application_deadline'] = Carbon::createFromFormat(
            'd/m/Y',
            $data['application_deadline']
        )->hour(23)->minute(59);

        $course->update($data);

        event(new SomethingNoteworthyHappened(auth()->user(), "Updated course {$data['code']}"));

        return redirect()->route('admin.course.index')->with('success', 'Course Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course, Request $request)
    {
        $code = $course->code;
        $course->students->each->delete();
        $course->delete();

        session()->flash('success', 'Course deleted');

        event(new SomethingNoteworthyHappened(auth()->user(), "Deleted course {$code}"));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'deleted']);
        }

        return redirect()->route('admin.course.index')->with('success', 'Course Deleted');
    }
}
