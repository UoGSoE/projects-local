@extends('layouts.app')

@section('content')

<div class="columns">
    <div class="column">

        <h3 class="title is-3">
            Edit course
        </h3>

        <form method="POST" action="{{ route('admin.course.update', $course->id) }}">
            @csrf

            @include('admin.course.partials.form')

            <hr />

            <div class="field">
                <div class="control">
                    <button class="button">Update Course</button>
                </div>
            </div>

        </form>
    </div>
    <div class="column">
        <h3 class="title is-3">
            Students
            <a href="{{ route('admin.course.enrollment', $course->id) }}" class="button">Upload student list</a>
        </h3>
        <ul>
            @foreach ($course->students as $student)
                <li>
                    <a href="{{ route('admin.user.show', $student->id) }}">
                        {{ $student->matric }} {{ $student->full_name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection