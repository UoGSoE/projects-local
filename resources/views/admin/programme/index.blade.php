@extends('layouts.app')

@section('content')

<h3 class="title is-3">
	Programmes
	<a href="{{ route('admin.programme.create') }}" class="button" title="Add new programme">
		+
	</a>
</h3>

<table class="table is-striped">
	<thead>
		<tr>
			<th>Title</th>
			<th>Type</th>
			<th>No. Projects</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($programmes as $programme)
			<tr>
				<td>
					<a href="{{ route('admin.programme.edit', $programme->id) }}">
						{{ $programme->title }}
					</a>
				</td>
				<td>{{ ucfirst($programme->category) }}</td>
				<td>{{ $programme->projects_count }}</td>
			</tr>
		@endforeach
	</tbody>
</table>

@endsection