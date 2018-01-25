@extends('layouts.app')

@section('content')

@foreach ($project->students as $student)
    <li>{{ $student->full_name }}</li>
@endforeach

@endsection
