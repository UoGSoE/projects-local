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
            <a class="button" href="{{ route('export.students.csv', $category) }}">
                ⬇️ Export CSV
            </a>
            @else
            <div class="dropdown is-hoverable">
                <div class="dropdown-trigger">
                    <button class="button" aria-haspopup="true" aria-controls="dropdown-menu4">
                        <span>Export</span>
                        <span class="icon is-small">
                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
                <div class="dropdown-menu" id="dropdown-menu4" role="menu">
                    <div class="dropdown-content">
                        <div class="dropdown-item">
                            <a class="button" href="{{ route('export.staff', 'xlsx') }}">
                                Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
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