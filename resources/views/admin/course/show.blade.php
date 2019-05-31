@extends('layouts.app')

@section('content')

<h3 class="title is-3">
	Details of Course {{ $course->code }}
	<a href="{{ route('admin.course.edit', $course->id) }}" class="button">
		Edit
	</a>
</h3>
<table class="table">
	<tr>
		<th>Code</th>
		<td>{{ $course->code }}</td>
	</tr>
	<tr>
		<th>Title</th>
		<td>{{ $course->title }}</td>
	</tr>
	<tr>
		<th>Type</th>
		<td>{{ ucfirst($course->category) }}</td>
	</tr>
	<tr>
		<th>Deadline</th>
		<td>{{ $course->application_deadline->format('d/m/Y') }}</td>
	</tr>
	<tr>
		<th>Staff can accept 1st choice students?</th>
		<td>{{ $course->allow_staff_accept ? 'Yes' : 'No' }}</td>
	</tr>
</table>

<div class="columns">
	<div class="column">
		<h4 class="title is-4">
			Students
			<a href="{{ route('admin.course.enrollment', $course->id) }}" class="button">Upload student list</a>
			<button class="button is-text is-pulled-right has-text-danger has-text-weight-semibold" @click.prevent="showConfirmation = true">Remove All Students</button>

		</h4>

		<course-student-list :students='@json($course->students)'></course-student-list>

	</div>
	<div class="column">
		<h4 class="title is-4">
			Projects
		</h4>
		<ul>
			@foreach ($course->projects as $project)
			<li>
				<a href="{{ route('project.show', $project->id) }}">
					{{ $project->title }}
				</a>
			</li>
			@endforeach
		</ul>
	</div>
</div>

<confirmation-dialog :show="showConfirmation" @cancel="showConfirmation = false" @confirm="deleteCourseStudents({{ $course->id }})">
	Do you really want to remove all students on this course from the system?
</confirmation-dialog>


@endsection