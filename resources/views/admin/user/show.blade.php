@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Details for <em>{{ $user->full_name }}</em>
    <form method="POST" action="{{ route('impersonate.start', $user->id) }}" class="is-pulled-right">
        @csrf
        <button class="button">Impersonate</button>
    </form>
</h3>

<table class="table">
    <tr>
        <th>Username</th>
        <td>{{ $user->username }}</td>
    </tr>
    <tr>
        <th>Email</th>
        <td>
            <a href="mailto:{{ $user->email }}">
                {{ $user->email }}
            </a>
        </td>
    </tr>
    <tr>
        <th>Type</th>
        <td>{{ $user->getType() }}</td>
    </tr>
</table>

@if ($user->isStaff())
    <h4 class="title is-4">Projects</h4>
@else
    <h4 class="title is-4">Project Choices</h4>
@endif

@endsection