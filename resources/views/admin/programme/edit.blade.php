@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Edit programme
</h3>

<form method="POST" action="{{ route('admin.programme.update', $programme->id) }}">
    @csrf

    @include('admin.programme.partials.form')

    <hr />

    <div class="field">
        <div class="control">
            <button class="button">Update Programme</button>
        </div>
    </div>

</form>
@endsection