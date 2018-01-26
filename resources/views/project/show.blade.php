@extends('layouts.app')

@section('content')

@can('accept-students', $project)
    @include('project.partials.student_list_form')
@else
    @include('project.partials.student_list')
@endcan

@endsection
