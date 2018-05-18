@extends('layouts.app')

@section('content')

<div class="columns">
    <div class="column">

        <h3 class="title is-3">
            Edit course
            <button class="button is-text has-text-danger has-text-weight-bold is-pulled-right" @click.prevent="showConfirmation = true">Delete Course</button>
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
        <course-student-list :students='@json($course->students)'></course-student-list>
    </div>
</div>

<confirmation-dialog :show="showConfirmation" @cancel="showConfirmation = false" @confirm="deleteCourse({{ $course->id }})">
    Do you really want to delete this course? This will also remove all the students on it.
</confirmation-dialog>

@endsection