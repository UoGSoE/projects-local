@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Edit project
</h3>

<form method="POST" action="{{ route('project.update', $project->id) }}">
    @csrf

    <input type="hidden" name="category" value="{{ old('category', $project->category) }}">

    @include('project.partials.form')

    <hr />

    <div class="field">
        <div class="control">
            <button class="button">Update Project</button>
        </div>
    </div>

</form>
@endsection
