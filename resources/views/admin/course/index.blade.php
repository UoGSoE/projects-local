@extends('layouts.app')

@section('content')

<nav class="level">
    <div class="level-left">
        <div class="level-item">
            <h3 class="title is-3">
                Courses
            </h3>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <div class="dropdown is-hoverable is-right">
                <div class="dropdown-trigger">
                    <button class="button" aria-haspopup="true" aria-controls="dropdown-menu">
                        <span>More</span>
                        <span class="icon is-small">
                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
                <div class="dropdown-menu" id="dropdown-menu" role="menu">
                    <div class="dropdown-content">
                        <a href="{{ route('admin.course.create') }}" class="dropdown-item">
							<i class="fas fa-plus"></i>
							Add new course
                        </a>
                        <hr class="dropdown-divider">
                        <a href="{{ route('export.courses', 'xlsx') }}" class="dropdown-item">
                            <i class="fas fa-file-excel"></i>
                            Export Excel
                        </a>
                        <a href="{{ route('export.courses', 'csv') }}" class="dropdown-item">
                            <i class="fas fa-file-csv"></i>
                            Export CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<table class="table is-striped is-fullwidth">
	<thead>
		<tr>
			<th>Code</th>
			<th>Title</th>
			<th>Type</th>
			<th>Deadline</th>
			<th class="has-text-centered">No. Projects</th>
			<th class="has-text-centered">No. Students</th>
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
			<td class="has-text-centered">{{ $course->projects_count }}</td>
			<td class="has-text-centered">{{ $course->students_count }}</td>
		</tr>
		@endforeach
	</tbody>
</table>

@endsection