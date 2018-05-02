@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Available Projects
</h3>
<p class="subtitle">
    Some blurb about choosing things
</p>

@if (Auth::user()->isntOnACourse())
    <div class="notification is-info">
        You do not seem to be registered on any project courses.  Please email the Engineering Teaching Office.
    </div>
@elseif (Auth::user()->isAccepted())
    <div class="notification is-info">
        You cannot choose new projects as you have already been accepted onto the project <em>{{ Auth::user()->projects()->first()->title }}</em>.
    </div>
@elseif (! Auth::user()->email)
    <div class="notification is-info">
        You cannot choose any projects as your email address is invalid.  Please email the Engineering Teaching Office.
    </div>
@else
    @if (Auth::user()->isTooLate())
        <div class="notification is-info">
            You cannot choose any projects as the application deadline has passed.
        </div>
    @endif
    <project-list :projects='@json($projects)' :programmes='@json($programmes)' :toolate='@json(Auth::user()->isTooLate())'></project-list>
@endif

@endsection
