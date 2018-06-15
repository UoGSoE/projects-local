@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Activity Logs
</h3>

{{ $logs->links() }}

<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th>Who</th>
            <th>When</th>
            <th>What</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($logs as $entry)
            <tr>
                <td>
                    <a href="{{ optional($entry->causer)->show_url }}">
                        {{ optional($entry->causer)->full_name }}
                    </a>
                </td>
                <td>
                    {{ $entry->created_at }}
                </td>
                <td>
                    {{ $entry->description }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $logs->links() }}

@endsection
