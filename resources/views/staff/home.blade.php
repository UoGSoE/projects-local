@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Your Projects
    <a href="{{ route('project.create', ['type' => 'undergrad']) }}" class="button is-outlined">
        + Undergrad
    </a>
    <a href="{{ route('project.create', ['type' => 'postgrad']) }}" class="button is-outlined">
        + Postgrad
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
        @foreach (auth()->user()->projects()->withCount('students')->orderBy('title')->get() as $project)
            <tr>
                <td>
                    <a href="{{ route('project.show', $project->id) }}">
                        @if ($project->isInactive())
                            <strike title="Inactive">{{ $project->title }}</strike>
                        @else
                            {{ $project->title }}
                        @endif
                    </a>
                </td>
                <td>
                    {{ ucfirst($project->category) }}
                </td>
                <td>
                    {{ $project->students_count }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
