@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Edit project
    <form method="POST" action="{{ route('project.delete', $project->id) }}" class="is-pulled-right">
        @csrf
        @method('DELETE')
        <button class="button is-text is-outlined has-text-danger has-text-weight-bold" @click.prevent="showConfirmation = true">Delete Project</button>
    </form>
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

<confirmation-dialog :show="showConfirmation" @cancel="showConfirmation = false" @confirm="deleteProject({{ $project->id }})">
    Do you really want to delete this project?
</confirmation-dialog>

@endsection
