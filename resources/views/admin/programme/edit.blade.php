@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Edit programme
    <button class="button is-text is-pulled-right has-text-danger has-text-weight-semibold" @click.prevent="showConfirmation = true">Delete Programme</button>
</h3>

<form method="POST" action="{{ route('admin.programme.update', $programme->id) }}">
    @csrf

    @include('admin.programme.partials.form')

    <hr />

    <div class="field">
        <div class="control">
            <button class="button">Update Programme</button>
        </div>
    </div>

</form>

<hr>

<h3 class="title is-3">Projects using this programme</h3>
@foreach ($programme->projects as $project)
    <li>
        <a href="{{ route('project.show', $project->id) }}">{{ $project->title }}</a>
        - run by <a href="{{ route('admin.user.show', $project->owner?->id) }}">{{ $project->owner?->full_name }}</a>
        - {{ $project->max_students }} max students
    </li>
@endforeach

<confirmation-dialog :show="showConfirmation" @cancel="showConfirmation = false" @confirm="deleteProgramme({{ $programme->id }})">
    Do you really want to delete this programme?
</confirmation-dialog>


@endsection
