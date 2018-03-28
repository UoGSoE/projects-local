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
</table>

<div class="columns">
	<div class="column">
		<h4 class="title is-4">
			Students
            <a href="{{ route('admin.course.enrollment', $course->id) }}" class="button">Upload student list</a>
		</h4>
		<ul>
		@foreach ($course->students as $student)
			<li>
				<a href="{{ route('admin.user.show', $student->id) }}">
					{{ $student->full_name }} ({{ $student->matric }})
				</a>
			</li>
		@endforeach
		</ul>
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

@endsection
