@extends('layouts.app')

@section('content')

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
@endsection