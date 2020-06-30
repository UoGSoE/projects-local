@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Enroll students onto <em>{{ $course->code }} {{ $course->title }}</em>
</h3>

<p class="subtitle">
    Note: This will <b>add</b> students to the course. Any students currently on
    the course will be left in place.
</p>
<p>
    Format should be :
    <pre>|Matric Number|Surname|Forenames|</pre> eg,
    <pre>|1234567|Smith|Jenny|</pre>
</p>

<form method="POST" action="{{ route('admin.course.enroll', $course->id) }}" enctype="multipart/form-data">
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
            <button class="button">Upload</button>
        </div>
    </div>
</form>

@endsection
