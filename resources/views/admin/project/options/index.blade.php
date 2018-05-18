@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    {{ ucfirst($category) }} Project Options
</h3>

<form method="POST" action="{{ route('admin.project.bulk-options.update', ['category' => $category]) }}">
@csrf
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
            <a
             :href="getProjectUrl(row.id)"
             :class="{ 'has-text-grey-light': !row.is_active }"
             :title="row.is_active ? '' : 'Inactive'"
            >
             @{{ row.title }}
            </a>
            <span v-if="row.is_confidential" class="icon is-small" title="Confidential">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M4 8V6a6 6 0 1 1 12 0v2h1a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-8c0-1.1.9-2 2-2h1zm5 6.73V17h2v-2.27a2 2 0 1 0-2 0zM7 6v2h6V6a3 3 0 0 0-6 0z"/></svg>
                </i>
            </span>
            <span v-if="row.is_placement" class="icon is-small" title="Placement">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 20S3 10.87 3 7a7 7 0 1 1 14 0c0 3.87-7 13-7 13zm0-11a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg>
                </i>
            </span>
        </template>
    </table-column>
    <table-column show="owner.full_name" label="Owner"></table-column>
    <table-column show="second_supervisor.full_name" label="2nd"></table-column>
    <table-column show="is_active" label="Active?">
        <template slot-scope="row">
            <input type="hidden" :name="'active[' + row.id + ']'" value="0">
            <input type="checkbox" class="checkbox" :name="'active[' + row.id + ']'" :value="1" :checked="row.is_active">
        </template>
    </table-column>
    <table-column show="" label="Delete?">
        <template slot-scope="row">
            <input type="hidden" :name="'delete[' + row.id + ']'" value="0">
            <input type="checkbox" class="checkbox is-danger" :name="'delete[' + row.id + ']'" :value="1">
        </template>
    </table-column>
    <table-column show="" :hidden="true" filter-on="course_codes"></table-column>
</table-component>

<hr />

<div class="field is-pulled-right">
    <div class="control">
        <button type="submit" class="button">Save Changes</button>
    </div>
</div>

</form>

@endsection