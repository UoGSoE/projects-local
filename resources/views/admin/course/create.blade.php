@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Create new course
</h3>

<form method="POST" action="{{ route('admin.course.store') }}">
    @csrf

    @include('admin.course.partials.form')

    <hr />

    <div class="field">
        <div class="control">
            <button class="button">Create Course</button>
        </div>
    </div>

</form>
@endsection