@extends('layouts.app')

@section('content')

<h3 class="title is-3">
	Details of Course {{ $course->code }}
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
</table>

<h4 class="title is-4">
	Students
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

@endsection
