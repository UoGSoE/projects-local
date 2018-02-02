@extends('layouts.app')

@section('content')

<table class="table">
    <tbody>
        <tr>
            <th>Title</th>
            <td>{{ $project->title }}</td>
        </tr>
        <tr>
            <th>Owner</th>
            <td>{{ $project->owner->full_name }}</td>
        </tr>
    </tbody>
</table>

@can('accept-students', $project)
    @include('project.partials.student_list_form')
@else
    @include('project.partials.student_list')
@endcan

@endsection
