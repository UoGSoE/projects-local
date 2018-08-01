@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Research areas
</h3>

@foreach ($areas as $area)
    <research-area :area="{{ $area->toJson() }}"></research-area>
@endforeach
<hr />
<new-research-area></new-research-area>
</div>


@endsection
