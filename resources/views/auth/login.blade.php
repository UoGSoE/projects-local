@extends('layouts.app')

@section('content')
<div class="columns">
    <div class="column is-one-third"></div>
    <div class="column is-one-third">
        <form method="POST" action="/login">
            @csrf
            <div class="field">
                <div class="control">
                    <label class="label">Username (GUID)</label>
                    <input class="input" name="username" type="text" autofocus>
                </div>
            </div>
            <div class="field">
                <div class="control">
                    <label class="label">Password</label>
                    <input class="input" name="password" type="password">
                </div>
            </div>
            <div class="field">
                <div class="control">
                    <label>
                        <input class="checkbox" name="remember_me" type="checkbox" value="1">
                        Remember me on this device?
                    </label>
                </div>
            </div>
            <hr>
            <div class="field">
                <div class="control">
                    <button class="button is-fullwidth is-info is-outlined">Log In</button>
                </div>
            </div>
        </form>
    </div>
    <div class="column is-one-third"></div>
</div>
@endsection
