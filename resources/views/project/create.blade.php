@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Create new {{ $project->category }} project
</h3>

<form method="POST" action="{{ route('project.store') }}">
    @csrf

    <input type="hidden" name="category" value="{{ old('category', $project->category) }}">

    @include('project.partials.form')

    <hr />

    <div class="field">
        <div class="control">
            <button class="button">Create Project</button>
        </div>
    </div>

</form>
@endsection
