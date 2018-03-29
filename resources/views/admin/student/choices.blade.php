@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    {{ ucfirst($category) }} Student Project Choices
</h3>

<table class="table is-fullwidth is-striped is-hoverable">
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
                    <a href="{{ route('admin.user.show', $student->id) }}">
                        {{ $student->full_name }}
                    </a>
                </td>
                @foreach (range(1, 5) as $choice)
                    <td>
                        @if ($student->projects->where('pivot.choice', $choice)->isNotEmpty())
                            @if ($student->projects->where('pivot.choice', $choice)->first()->pivot->is_accepted)
                                &#10003;
                            @else
                                <input type="radio" name="students[{{ $student->id}}]" value="{{ $student->projects->where('pivot.choice', $choice)->first()->id }}">
                            @endif
                            {{ $student->projects->where('pivot.choice', $choice)->first()->title }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
