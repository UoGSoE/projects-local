@extends('layouts.app')

@section('content')

<h3 class="title is-3">
	Programmes
	<a href="{{ route('admin.programme.create') }}" class="button is-text" title="Add new programme">
		<span class="icon">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M11 9h4v2h-4v4H9v-4H5V9h4V5h2v4zm-1 11a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16z"/></svg>
		</span>
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