@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    All Users
</h3>

<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
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
