@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Student Project Choices
</h3>

<table class="table is-fullwidth is-striped">
    <thead>
        <tr>
            <th>Student</th>
            <th>1st</th>
            <th>2nd</th>
            <th>3rd</th>
            <th>4th</th>
            <th>5th</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($students as $student)
            <tr>
                <td>
                    {{ $student->full_name }}
                </td>
                <td>
                    @if ($student->projects->where('pivot_choice', 1)->isNotEmpty())
                        {{ $student->projects->where('pivot_choice', 1)->first()->title }}
                    @endif
                </td>
                <td>
                    @if ($student->projects->where('pivot_choice', 2)->isNotEmpty())
                        {{ $student->projects->where('pivot_choice', 2)->first()->title }}
                    @endif
                </td>
                <td>
                    @if ($student->projects->where('pivot_choice', 3)->isNotEmpty())
                        {{ $student->projects->where('pivot_choice', 3)->first()->title }}
                    @endif
                </td>
                <td>
                    @if ($student->projects->where('pivot_choice', 4)->isNotEmpty())
                        {{ $student->projects->where('pivot_choice', 4)->first()->title }}
                    @endif
                </td>
                <td>
                    @if ($student->projects->where('pivot_choice', 5)->isNotEmpty())
                        {{ $student->projects->where('pivot_choice', 5)->first()->title }}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
