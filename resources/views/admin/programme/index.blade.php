@extends('layouts.app')

@section('content')

<nav class="level">
    <div class="level-left">
        <div class="level-item">
            <h3 class="title is-3">
                Programmes
            </h3>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <div class="dropdown is-hoverable is-right">
                <div class="dropdown-trigger">
                    <button class="button" id="dropdown-trigger" aria-haspopup="true" aria-controls="dropdown-menu">
                        <span>More</span>
                        <span class="icon is-small">
                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
                <div class="dropdown-menu" role="menu">
                    <div class="dropdown-content">
                        <a href="{{ route('admin.programme.create') }}" id="add-programme" class="dropdown-item">
							<i class="fas fa-plus"></i>
							Add new programme
                        </a>
                        <hr class="dropdown-divider">
                        <a href="{{ route('export.programmes', 'xlsx') }}" class="dropdown-item">
                            <i class="fas fa-file-excel"></i>
                            Export Excel
                        </a>
                        <a href="{{ route('export.programmes', 'csv') }}" class="dropdown-item">
                            <i class="fas fa-file-csv"></i>
                            Export CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<filterable-items :items='@json($programmes)'>
    <span  slot-scope="{ items: programmes, inputAttrs, inputEvents, sortOn }">
        <input class="input" type="text" v-bind="inputAttrs" v-on="inputEvents" placeholder="Filter table..." autofocus>
        <table class="table is-fullwidth is-striped is-hover">
            <thead>
                <tr>
                    <th @click.prevent="sortOn('title')" class="cursor-pointer">Name</th>
                    <th @click.prevent="sortOn('plan_code')" class="cursor-pointer">Plan Code</th>
                    <th @click.prevent="sortOn('category')" class="cursor-pointer">Category</th>
                    <th @click.prevent="sortOn('projects_count')" class="cursor-pointer has-text-centered">Projects</th>
                    <th @click.prevent="sortOn('places_count')" class="cursor-pointer has-text-centered">Places</th>
                    <th @click.prevent="sortOn('accepted_count')" class="cursor-pointer has-text-centered">Accepted</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="programme in programmes" :key="programme.id">
                    <td>
                        <a :href="editProgrammeUrl(programme.id)">@{{ programme.title }}</a>
                    </td>
                    <td>@{{ programme.plan_code }}</td>
                    <td>@{{ programme.category }}</td>
                    <td class="has-text-centered">@{{ programme.projects_count }}</td>
                    <td class="has-text-centered">@{{ programme.places_count }}</td>
                    <td class="has-text-centered">@{{ programme.accepted_count }}</td>
                </tr>
            </tbody>
        </table>
    </span>
</filterable-items>

@endsection
