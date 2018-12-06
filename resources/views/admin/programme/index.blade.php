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

<filterable-items :items='@json($programmes)'>
    <span  slot-scope="{ items: programmes, inputAttrs, inputEvents, sortOn }">
        <input class="input" type="text" v-bind="inputAttrs" v-on="inputEvents" placeholder="Filter table..." autofocus>
        <table class="table is-fullwidth is-striped is-hover">
            <thead>
                <tr>
                    <th @click.prevent="sortOn('title')" class="cursor-pointer">Name</th>
                    <th @click.prevent="sortOn('category')" class="cursor-pointer">Category</th>
                    <th @click.prevent="sortOn('projects_count')" class="cursor-pointer">Projects</th>
                    <th @click.prevent="sortOn('places_count')" class="cursor-pointer">Places</th>
                    <th @click.prevent="sortOn('accepted_count')" class="cursor-pointer">Accepted</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="programme in programmes" :key="programme.id">
                    <td>
                        <a :href="editProgrammeUrl(programme.id)">@{{ programme.title }}</a>
                    </td>
                    <td>@{{ programme.category }}</td>
                    <td>@{{ programme.projects_count }}</td>
                    <td>@{{ programme.places_count }}</td>
                    <td>@{{ programme.accepted_count }}</td>
                </tr>
            </tbody>
        </table>
    </span>
</filterable-items>

@endsection