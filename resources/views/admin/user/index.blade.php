@extends('layouts.app')

@section('content')

<nav class="level">
    <div class="level-left">
        <div class="level-item">
            <h3 class="title is-3">
                {{ ucfirst(str_plural($category)) }}
            </h3>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <div class="dropdown is-hoverable is-right">
                <div class="dropdown-trigger">
                    <button class="button" aria-haspopup="true" aria-controls="dropdown-menu">
                        <span>More</span>
                        <span class="icon is-small">
                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
                <div class="dropdown-menu" id="dropdown-menu" role="menu">
                    <div class="dropdown-content">
                        <a href="{{ route('export.'.$category, 'xlsx') }}" class="dropdown-item">
                            <i class="fas fa-file-excel"></i>
                            Export Excel
                        </a>
                        <a href="{{ route('export.'.$category, 'csv') }}" class="dropdown-item">
                            <i class="fas fa-file-csv"></i>
                            Export CSV
                        </a>
                        @if ($category !== 'staff')
                        <hr class="dropdown-divider">
                        <a class="dropdown-item has-text-danger has-text-weight-semibold" @click.prevent="showConfirmation = true">
                            <i class="fas fa-trash"></i>
                            Remove all {{ $category }} students
                        </a>
                        @endif
                    </div>
                </div>
            </div>
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