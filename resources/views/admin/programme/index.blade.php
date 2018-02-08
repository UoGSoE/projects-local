@extends('layouts.app')

@section('content')

<h3 class="title is-3">
	Programmes
</h3>
<ul>
	@foreach ($programmes as $programme)
		<li>
			<a href="">
				{{ $programme->title }}
			</a>
		</li>
	@endforeach
</ul>

@endsection