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

<div class="columns">
    <div class="column">
        <student-list :project='@json($project)' :students='{{ $project->studentsAsJson() }}'></student-list>
        @can('accept-students', $project)
            @include('project.partials.student_list_form')
        @else
            @include('project.partials.student_list')
        @endcan
    </div>
    <div class="column">
        <div id="student_profile_box" v-if="selectedStudent">
            <article class="media">
              <div class="media-content">
                <div class="content">
                  <p>
                    Profile for <strong>@{{ selectedStudent.full_name}}</strong> <small>@{{ selectedStudent.email }}</small>
                    <br>
                    <span v-html="selectedStudent.profile"></span>
                  </p>
                </div>
              </div>
            </article>
        </div>
    </div>
</div>

@endsection
