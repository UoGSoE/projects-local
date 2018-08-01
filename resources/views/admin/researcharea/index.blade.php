@extends('layouts.app')

@section('content')

<research-area-admin :areas="{{ $areas->toJson() }}"></research-area-admin>

@endsection
