@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Your Projects
    <a href="{{ route('project.create', ['type' => 'undergrad']) }}" class="button is-info is-outlined">
        + Undergrad Project
    </a>
    <a href="{{ route('project.create', ['type' => 'postgrad']) }}" class="button is-success is-outlined">
        + Postgrad Project
    </a>
</h3>
<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th>Title</th>
            <th>Type</th>
            <th>No. Students Applied</th>
        </tr>
    </thead>
    <tbody>
        @foreach (auth()->user()->projects()->orderBy('title')->get() as $project)
            <tr>
                <td>
                    <a href="{{ route('project.show', $project->id) }}">
                        {{ $project->title }}
                    </a>
                </td>
                <td>
                    {{ ucfirst($project->category) }}
                </td>
                <td>
                    {{ $project->students()->count() }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
