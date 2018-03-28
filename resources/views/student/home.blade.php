@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Available Projects
</h3>
<p class="subtitle">
    Some blurb about choosing things
</p>

@if (Auth::user()->isntOnACourse())
    You do not seem to be registered on any project courses.  Please email the Engineering Teaching Office.
@else
    <project-list :projects='@json($projects)' :programmes='@json($programmes)'></project-list>
@endif

@endsection
