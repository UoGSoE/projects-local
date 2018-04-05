@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Edit programme
    <button class="button is-text is-pulled-right has-text-danger has-text-weight-semibold" @click.prevent="showConfirmation = true">Delete Programme</button>
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

<confirmation-dialog :show="showConfirmation" @cancel="showConfirmation = false" @confirm="deleteProgramme({{ $programme->id }})">
    Do you really want to delete this programme?
</confirmation-dialog>

@endsection