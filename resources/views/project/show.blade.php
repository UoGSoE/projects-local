@extends('layouts.app')

@section('content')

<div class="columns">
    <div class="column">
        <h3 class="title is-3">
            Details of project <em>{{ $project->title }}</em>
        </h3>
        <a href="{{ route('project.edit', $project->id) }}" class="button">
            Edit
        </a>
        <a href="{{ route('project.copy', $project->id) }}" class="button">
            Make {{ $project->isUndergrad() ? 'a postgrad' : 'an undergrad' }} copy
        </a>
        <hr />
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
                    <th>Placement?</th>
                    <td>{{ $project->isPlacement() ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <th>Confidential?</th>
                    <td>{{ $project->isConfidential() ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <th>Owner</th>
                    <td>
                        @if (Auth::user()->isAdmin())
                            <a href="{{ route('admin.user.show', $project->owner->id) }}">
                                {{ $project->owner->full_name }}
                            </a>
                        @else
                            {{ $project->owner->full_name }}
                        @endif
                    </td>
                </tr>
                @if ($project->hasSecondSupervisor())
                    <tr>
                        <th>Second Supervisor</th>
                        <td>
                            @if (Auth::user()->isAdmin())
                                <a href="{{ route('admin.user.show', $project->secondSupervisor->id) }}">
                                    {{ $project->secondSupervisor->full_name }}
                                </a>
                            @else
                                {{ $project->secondSupervisor->full_name }}
                            @endif
                        </td>
                    </tr>
                @endif
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
                            <li>
                                @if (Auth::user()->isAdmin())
                                    <a href="{{ route('admin.course.show', $course->id) }}">
                                        {{ $course->code }} {{ $course->title }}
                                    </a>
                                @else
                                    {{ $course->code }} {{ $course->title }}
                                @endif
                            </li>
                        @endforeach
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>Programmes</th>
                    <td>
                        <ul class="is-inline">
                        @foreach ($project->programmes as $programme)
                            <li>
                                @if (Auth::user()->isAdmin())
                                    <a href="{{ route('admin.programme.edit', $programme->id) }}">
                                        {{ $programme->title }}
                                    </a>
                                @else
                                    {{ $programme->title }}
                                @endif
                            </li>
                        @endforeach
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="column">
        <student-list :project='@json($project)' :students='{{ $project->studentsAsJson() }}' v-on:showprofile="selectedStudent = $event"></student-list>

        @if (Auth::user()->isAdmin())
            <hr />
            <manual-student-allocator :students='@json($students)' :project='@json($project)'></manual-student-allocator>
        @endif
    </div>
</div>

@endsection
