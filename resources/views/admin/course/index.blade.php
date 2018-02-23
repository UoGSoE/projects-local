@extends('layouts.app')

@section('content')

<h3 class="title is-3">
	Courses
	<a href="{{ route('admin.course.create') }}" class="button" title="Add new course">
		+
	</a>
</h3>

<table class="table is-striped">
	<thead>
		<tr>
            <th>Code</th>
			<th>Title</th>
			<th>Type</th>
			<th>No. Projects</th>
			<th>No. Students</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($courses as $course)
			<tr>
                <td>
                    <a href="{{ route('admin.course.edit', $course->id) }}">
                        {{ $course->code }}
                    </a>
                </td>
				<td>{{ $course->title }}</td>
				<td>{{ ucfirst($course->category) }}</td>
				<td>{{ $course->projects_count }}</td>
				<td>{{ $course->students_count }}</td>
			</tr>
		@endforeach
	</tbody>
</table>

@endsection