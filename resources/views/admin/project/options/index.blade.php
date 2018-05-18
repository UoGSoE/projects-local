@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    {{ ucfirst($category) }} Project Options
</h3>

<project-bulk-options :projects='@json($projects)'></project-bulk-options>

@endsection