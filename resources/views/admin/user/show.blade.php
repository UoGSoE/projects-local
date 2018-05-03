@extends('layouts.app')

@section('content')

<nav class="level">
    <div class="level-left">
        <h3 class="title is-3 level-item">
            <p class="">Details for <em>{{ $user->full_name }}</em>&nbsp;</p>
            @if ($user->isStaff() and (Auth::user()->id != $user->id))
                <admin-toggle :user='@json($user)'></admin-toggle>
            @endif
            <form method="POST" action="{{ route('impersonate.start', $user->id) }}" class="">
                @csrf
                <button class="button">Impersonate</button>
            </form>
            <a href="{{ route('gdpr.export.user', $user->id) }}" class="button">
                GDPR Export
            </a>
        </h3>
    </div>
    <div class="level-right">
        <button class="button is-text is-pulled-right has-text-danger has-text-weight-semibold level-item" @click.prevent="showConfirmation = true">Delete User</button>
    </div>
</nav>

<table class="table">
    <tr>
        <th>Username</th>
        <td>{{ $user->username }}</td>
    </tr>
    <tr>
        <th>Email</th>
        <td>
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <a href="mailto:{{ $user->email }}">
                            {{ $user->email }}
                        </a>
                    </div>
                    <div class="level-item">
                        <email-edit :user='@json($user)'></email-edit>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <th>Type</th>
        <td>
            {{ $user->getType() }}
        </td>
    </tr>
    @if ($user->profile)
        <tr>
            <th>Profile</th>
            <td>{!! $user->getFormattedProfile() !!}</td>
        </tr>
    @endif
    @if ($user->course)
        <tr>
            <th>Course</th>
            <td>
                <a href="{{ route('admin.course.show', $user->course->id) }}">
                    {{ $user->course->code }} {{ $user->course->title }}
                </a>
            </td>
        </tr>
    @endif
</table>

@if ($user->isStaff())
    <h4 class="title is-4">Projects</h4>
    <ul>
        @foreach ($user->projects as $project)
            <li>
                <a href="{{ route('project.show', $project->id) }}">
                    {{ $project->title }}
                </a>
            </li>
        @endforeach
    </ul>
@else
    <h4 class="title is-4">Project Choices</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Choice</th>
                <th>Accepted?</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($user->projects as $project)
            <tr>
                <td>
                    <a href="{{ route('project.show', $project->id) }}">
                        {{ $project->title }}
                    </a>
                </td>
                <td>{{ $project->pivot->choice }}</td>
                <td>{{ $project->pivot->is_accepted ? 'Yes' : 'No' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

<confirmation-dialog :show="showConfirmation" @cancel="showConfirmation = false" @confirm="deleteUser({{ $user->id }})">
    Do you really want to delete this user?  This will also delete all of their projects.
</confirmation-dialog>

@endsection
