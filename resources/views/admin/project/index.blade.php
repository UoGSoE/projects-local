@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    All {{ ucfirst($category) }} Projects
</h3>

<table-component
    :data='@json($projects)'
    sort-by="title"
    sort-order="asc"
    table-class="table is-fullwidth is-striped is-hover"
    thead-class="cursor-pointer"
    :show-caption="false"
    filter-input-class="input"
    >
    <table-column show="title" label="Title">
        <template slot-scope="row">
            <a :href="`/project/${row.id}`">@{{ row.title }}</a>
        </template>
    </table-column>
    <table-column show="owner.full_name" label="Owner"></table-column>
    <table-column show="category" label="Category"></table-column>
    <table-column show="students_count" label="Students Applied"></table-column>
    <table-column show="accepted_students_count" label="Accepted"></table-column>
    <table-column show="" :hidden="true" filter-on="course_codes"></table-column>
</table-component>

@endsection