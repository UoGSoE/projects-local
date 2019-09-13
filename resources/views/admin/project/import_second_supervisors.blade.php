@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Import Second Supervisors
</h3>

<!-- TODO -->
<pre>
    Project ID | Project Name    | Col 2 | Col 3 | Supervisor GUID
    4          | Amazing project | Col 2 | Col 3 | abc1x
</pre>

<hr>

<form id="form" method="POST" action="{{ route('admin.import.second_supervisors') }}" enctype="multipart/form-data">
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