@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    All Projects
</h3>

<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th>Title</th>
            <th>Owner</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($projects as $project)
            <tr>
                <td>{{ $project->title }}</td>
                <td>{{ $project->owner->full_name }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection