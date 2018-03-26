@extends('layouts.app')

@section('content')

<nav class="level">
  <div class="level-left">
    <div class="level-item">
        <h3 class="title is-3">
            All Users
        </h3>
    </div>
  </div>
</nav>

<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th>Username</th>
            <th>Surname</th>
            <th>Forenames</th>
            <th>Type</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr>
                <td>
                    <a href="{{ route('admin.user.show', $user->id) }}">
                        {{ $user->username }}
                    </a>
                </td>
                <td>
                    {{ $user->surname }}
                </td>
                <td>
                    {{ $user->forenames }}
                </td>
                <td>
                    {{ $user->getType() }}
                </td>
                <td>
                    <a href="mailto:{{ $user->email }}">
                        {{ $user->email }}
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
