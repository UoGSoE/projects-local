@extends('layouts.app')

@section('content')

<h3 class="title is-3">
	Courses
	<a href="{{ route('admin.course.create') }}" class="button is-text" title="Add new course">
		<span class="icon">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
				<path d="M11 9h4v2h-4v4H9v-4H5V9h4V5h2v4zm-1 11a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16z" /></svg>
		</span>
	</a>
</h3>

<table class="table is-striped is-fullwidth">
	<thead>
		<tr>
			<th>Code</th>
			<th>Title</th>
			<th>Type</th>
			<th>Deadline</th>
			<th>No. Projects</th>
			<th>No. Students</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($courses as $course)
		<tr>
			<td>
				<a href="{{ route('admin.course.show', $course->id) }}">
					{{ $course->code }}
					@if ($course->allow_staff_accept) <span title="Staff can accept 1st choice students"> 🥇 </span> @endif
				</a>
			</td>
			<td>{{ $course->title }}</td>
			<td>{{ ucfirst($course->category) }}</td>
			<td>{{ $course->application_deadline->format('d/m/Y') }}</td>
			<td>{{ $course->projects_count }}</td>
			<td>{{ $course->students_count }}</td>
		</tr>
		@endforeach
	</tbody>
</table>

@endsection