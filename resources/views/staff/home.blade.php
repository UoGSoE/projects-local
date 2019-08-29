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
            <th>No. Students Accepted</th>
        </tr>
    </thead>
    <tbody>
        @foreach (auth()->user()->projects()->withCount([
        'students',
        'students as accepted_students_count' => function ($query) {
        return $query->where('is_accepted', '=', true);
        },
        ])->orderBy('title')->get() as $project)
        <tr>
            <td>
                <a href="{{ route('project.show', $project->id) }}">
                    @if ($project->isInactive())
                    <span class="tag">Inactive</span>
                    @endif
                    {{ $project->title }}
                </a>
            </td>
            <td>
                {{ ucfirst($project->category) }} @if($project->type) ({{ $project->type }}) @endif
            </td>
            <td>
                {{ $project->students_count }}
            </td>
            <td>
                {{ $project->accepted_students_count }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection