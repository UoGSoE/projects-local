@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Import placement projects
</h3>
<!-- TODO -->
<pre>
    Category | Title | Description | Pre Req | Active | Placement | Confidential | GUID | Max Students | Course Code | Programme Name | Student Matric | Student Surname
</pre>

<hr>

<form id="form" method="POST" action="{{ route('admin.import.placements') }}" enctype="multipart/form-data">
    @csrf
    <div class="file">
        <label class="file-label">
            <input class="file-input" type="file" name="sheet">
            <span class="file-cta">
                <span class="file-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13 10v6H7v-6H2l8-8 8 8h-5zM0 18h20v2H0v-2z"/></svg>
                </span>
                <span class="file-label">
                    Choose a spreadsheet
                </span>
            </span>
        </label>
    </div>
    <hr />
    <div class="field">
        <div class="control">
            <button class="button" id="submit-button">Upload</button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    const form = document.querySelector('#form');
    form.addEventListener('submit', function () {
        const button = document.querySelector('#submit-button');
        button.classList.add('is-loading');
    });
</script>
@endpush