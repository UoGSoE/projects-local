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
            @if ($category !== 'staff')
                <button class="button is-text is-pulled-right has-text-danger has-text-weight-semibold" @click.prevent="showConfirmation = true">Remove all {{ $category }} students</button>
            @endif
    </div>
   </div>
</nav>

<table-component
    :data='@json($users)'
    sort-by="surname"
    sort-order="asc"
    table-class="table is-fullwidth is-striped is-hover"
    :show-caption="false"
    filter-input-class="input"
    >
    <table-column label="Admin?" :sortable="false" :filterable="false">
        <template slot-scope="row">
            <admin-toggle :user='row'></admin-toggle>
        </template>
    </table-column>
    <table-column show="username" label="User">
        <template slot-scope="row">
            <a :href="`/admin/user/${row.id}`">@{{ row.username }}</a>
        </template>
    </table-column>
    <table-column show="surname" label="Surname"></table-column>
    <table-column show="forenames" label="Forename"></table-column>
    <table-column show="type" label="Type"></table-column>
    <table-column show="email" label="Email">
        <template slot-scope="row">
            <a :href="`mailto:${row.email}`">@{{ row.email }}</a>
        </template>
    </table-column>
</table-component>

<confirmation-dialog :show="showConfirmation" @cancel="showConfirmation = false" @confirm="deleteStudents('{{ $category }}')">
    Do you really want to remove all {{ $category }} students from the system?
</confirmation-dialog>

@endsection
