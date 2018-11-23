@extends('layouts.app')

@section('content')

<nav class="level">
  <div class="level-left">
    <div class="level-item">
        <h3 class="title is-3">
            {{ ucfirst(str_plural($category)) }}
        </h3>
        &nbsp;
        @if ($category != 'staff')
            <a class="button" href="{{ route('admin.report.choices', $category) }}">
                Student Choices
            </a>
        @endif
        &nbsp;
        <new-user></new-user>
    </div>
  </div>
  <div class="level-right">
    <div class="level-item">
            @if ($category !== 'staff')
                <button class="button is-text is-pulled-right has-text-danger has-text-weight-semibold" @click.prevent="showConfirmation = true">Remove all {{ $category }} students</button>
            @endif
    </div>
   </div>
</nav>

@if ($category == 'staff')
    @include('admin.user.partials.staff_table')
@else
    @include('admin.user.partials.student_table')
@endif
<confirmation-dialog :show="showConfirmation" @cancel="showConfirmation = false" @confirm="deleteStudents('{{ $category }}')">
    Do you really want to remove all {{ $category }} students from the system?
</confirmation-dialog>

@endsection
