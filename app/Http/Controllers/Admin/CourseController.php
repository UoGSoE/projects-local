<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Events\SomethingNoteworthyHappened;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.course.index', [
            'courses' => Course::orderBy('title')->withCount(['projects', 'students'])->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.course.create', [
            'course' => new Course(['application_deadline' => now()->addMonths(3)]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'code' => 'required|unique:courses',
            'category' => 'required|in:undergrad,postgrad',
            'application_deadline' => 'required|date_format:d/m/Y',
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
     *
     * @param  \App\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function show(Course $course)
    {
        return view('admin.course.show', [
            'course' => $course,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function edit(Course $course)
    {
        return view('admin.course.edit', [
            'course' => $course,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'title' => 'required',
            'code' => ['required', Rule::unique('courses')->ignore($course->id)],
            'category' => 'required|in:undergrad,postgrad',
            'application_deadline' => 'required|date_format:d/m/Y',
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
     * @param  \App\Course  $course
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
