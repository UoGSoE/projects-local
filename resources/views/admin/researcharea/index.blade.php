@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Research areas
</h3>

<research-area-admin :areas="{{ $areas->toJson() }}"></research-area-admin>

@endsection
