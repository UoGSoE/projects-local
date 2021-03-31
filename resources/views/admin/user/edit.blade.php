@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Edit {{ $user->full_name }}
</h3>

<form method="POST" action="{{ route('admin.user.update', $user->id) }}">
    @csrf

    <div class="field">
        <div class="control">
            <label class="label">Forenames</label>
            <input class="input" name="forenames" type="text" value="{{ old('forenames', $user->forenames) }}" required>
        </div>
    </div>

    <div class="field">
        <div class="control">
            <label class="label">Surname</label>
            <input class="input" name="surname" type="text" value="{{ old('surname', $user->surname) }}" required>
        </div>
    </div>

    <div class="field">
        <div class="control">
            <label class="label">GUID</label>
            <input class="input" name="username" type="text" value="{{ old('username', $user->username) }}" required>
        </div>
    </div>


    <div class="field">
        <div class="control">
            <label class="label">Email</label>
            @if ($user->isStudent())
                <input class="input has-background-light" name="email" type="text" value="{{ old('email', $user->email) }}" required readonly>
            @else
                <input class="input" name="email" type="text" value="{{ old('email', $user->email) }}" required>
            @endif
        </div>
    </div>
    @if ($user->isStudent())
        <p class="help"><b>Note: </b> as this is a student, their email will be "[GUID]@student.gla.ac.uk" when saved</p>
    @endif

    <hr />

    <div class="field">
        <div class="control">
            <button class="button">Update user</button>
        </div>
    </div>

</form>

@endsection
