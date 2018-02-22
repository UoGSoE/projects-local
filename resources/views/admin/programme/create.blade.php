@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Create new programme
</h3>

<form method="POST" action="{{ route('admin.programme.store') }}">
    @csrf

    @include('admin.programme.partials.form')

    <hr />

    <div class="field">
        <div class="control">
            <button class="button">Create Programme</button>
        </div>
    </div>

</form>
@endsection