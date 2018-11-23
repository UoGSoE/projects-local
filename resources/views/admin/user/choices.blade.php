@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    All {{ ucfirst($category) }} Student Choices
</h3>

<table-component
    :data='@json($students)'
    sort-by="title"
    sort-order="asc"
    table-class="table is-fullwidth is-striped is-hover"
    thead-class="cursor-pointer"
    :show-caption="false"
    filter-input-class="input"
    >
    <table-column show="full_name" label="Name">
        <template slot-scope="row">
            <a
             :href="row.id"
            >
             @{{ row.full_name }}
            </a>
            <span v-for="project in row.projects" :key="`student${row.id}project${project.id}`">Hello</span>
        </template>
    </table-column>
</table-component>

@endsection
