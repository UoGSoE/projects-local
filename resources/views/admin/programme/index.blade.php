@extends('layouts.app')

@section('content')

<h3 class="title is-3">
	Programmes
	<a id="add-programme" href="{{ route('admin.programme.create') }}" class="button is-text" title="Add new programme">
		<span class="icon">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M11 9h4v2h-4v4H9v-4H5V9h4V5h2v4zm-1 11a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16z"/></svg>
		</span>
	</a>
</h3>

<table-component
    :data='@json($programmes)'
    sort-by="title"
    sort-order="asc"
    table-class="table is-fullwidth is-striped is-hover"
    :show-caption="false"
    filter-input-class="input"
    >
    <table-column show="title" label="Name">
        <template slot-scope="row">
            <a :href="editProgrammeUrl(row.id)">@{{ row.title }}</a>
        </template>
    </table-column>
    <table-column show="category" label="Category"></table-column>
    <table-column show="projects_count" label="No. Projects"></table-column>
    <table-column show="places_count" label="No. Places"></table-column>
    <table-column show="accepted_count" label="No. Accepted"></table-column>
</table-component>

@endsection