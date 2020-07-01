@extends('layouts.app')

@section('content')

<h3 class="title is-3">Import Old Projects</h3>

<form action="{{ route('import.oldprojects') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <p class="help"><b>Note: </b> the spreadsheet is imported in the background, so the data might
    not show up for a few minutes in the website.</p>
    <div class="field">
        <div class="control">
            <label class="label">Spreadsheet</label>
            <input class="input" name="sheet" type="file" required>
        </div>
    </div>
    <div class="field">
        <div class="control">
            <button class="button">Upload</button>
        </div>
    </div>

</form>
@endsection