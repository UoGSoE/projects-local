@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Merge and Remove Programmes
</h3>
<p class="subtitle">
    <b>Note:</b> This will merge all the projects <em>and students</em> from the <b>Merge From</b> programme(s) into the <b>Merge To</b> programme.
</p>

@livewire('programme-merger')

@endsection
