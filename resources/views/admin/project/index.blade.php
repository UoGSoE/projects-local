@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    All {{ ucfirst($category) }} Projects
</h3>

<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th>Title</th>
            <th>Owner</th>
            <th>Type</th>
            <th>Students Applied</th>
            <th>Accepted</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($projects as $project)
            <tr>
                <td>
                    <a href="{{ route('project.show', $project->id) }}">
                        {{ $project->title }}
                    </a>
                </td>
                <td>
                    <a href="{{ route('admin.user.show', $project->staff_id) }}">
                        {{ $project->owner->full_name }}
                    </a>
                </td>
                <td>{{ ucfirst($project->category) }}</td>
                <td>{{ $project->students_count }}</td>
                <td>{{ $project->accepted_students_count }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection