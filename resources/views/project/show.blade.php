@extends('layouts.app')

@section('content')

<div class="columns">
    <div class="column">
        <h3 class="title is-3">
            <a href="{{ route('project.edit', $project->id) }}" class="button">
                Edit
            </a>
            Details of project <em>{{ $project->title }}</em>
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
    </div>

    <div class="column">
        <student-list :project='@json($project)' :students='{{ $project->studentsAsJson() }}' v-on:showprofile="selectedStudent = $event"></student-list>

        <div id="student_profile_box" v-if="selectedStudent">
            <p>&nbsp;</p>
            <div class="card">
                <div class="card-content">
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
    </div>
</div>

@endsection
