@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Details of project <em>{{ $project->title }}</em>
    <a href="{{ route('project.edit', $project->id) }}" class="button">
        Edit
    </a>
</h3>
<table class="table">
    <tbody>
        <tr>
            <th>Title</th>
            <td>{{ $project->title }}</td>
        </tr>
        <tr>
            <th>Description</th>
            <td>{{ $project->description }}</td>
        </tr>
        <tr>
            <th>Pre-requisit Skills</th>
            <td>{{ $project->pre_req }}</td>
        </tr>
        <tr>
            <th>Active?</th>
            <td>{{ $project->isActive() ? 'Yes' : 'No' }}</td>
        </tr>
        <tr>
            <th>Owner</th>
            <td>{{ $project->owner->full_name }}</td>
        </tr>
        <tr>
            <th>Type</th>
            <td>{{ ucfirst($project->category) }}</td>
        </tr>
        <tr>
            <th>Max Students</th>
            <td>{{ $project->max_students }}</td>
        </tr>
        <tr>
            <th>Courses</th>
            <td>
                <ul class="is-inline">
                @foreach ($project->courses as $course)
                    <li>{{ $course->code }} {{ $course->title }}</li>
                @endforeach
                </ul>
            </td>
        </tr>
        <tr>
            <th>Programmes</th>
            <td>
                <ul class="is-inline">
                @foreach ($project->programmes as $programme)
                    <li>{{ $programme->title }}</li>
                @endforeach
                </ul>
            </td>
        </tr>
    </tbody>
</table>

@can('accept-students', $project)
    @include('project.partials.student_list_form')
@else
    @include('project.partials.student_list')
@endcan

@endsection
