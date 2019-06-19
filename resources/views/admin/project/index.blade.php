@extends('layouts.app')

@section('content')

<nav class="level">
    <div class="level-left">
        <div class="level-item">
            <h3 class="title is-3">
                All {{ ucfirst($category) }} Projects
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
                        <a href="{{ route('admin.project.bulk-options', ['category' => $category]) }}" class="dropdown-item">
                            Bulk Options
                        </a>
                        <hr class="dropdown-divider">
                        <a href="{{ route('export.projects', ['category' => $category, 'format' => 'xlsx']) }}" class="dropdown-item">
                            <i class="fas fa-file-excel"></i>
                            Export Excel
                        </a>
                        <a href="{{ route('export.projects', ['category' => $category, 'format' => 'csv']) }}" class="dropdown-item">
                            <i class="fas fa-file-csv"></i>
                            Export CSV
                        </a>
                        <hr class="dropdown-divider">
                        <a href="{{ route('admin.import.second_supervisors.show') }}" class="dropdown-item">
                            Import 2nd Supervisors
                        </a>
                        <a href="{{ route('admin.import.placements.show') }}" class="dropdown-item">
                            Import Placements
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<filterable-items :items='@json($projects)' searchables="title,course_codes,programe_titles,owner_name,student_names">
    <span  slot-scope="{ items: projects, inputAttrs, inputEvents, sortOn }">
        <input class="input" type="text" v-bind="inputAttrs" v-on="inputEvents" placeholder="Filter table..." autofocus>
        <table class="table is-fullwidth is-striped is-hover">
            <thead>
                <tr>
                    <th @click.prevent="sortOn('title')" class="cursor-pointer">Title</th>
                    <th @click.prevent="sortOn('owner_name')" class="cursor-pointer">Owner</th>
                    <th class="cursor-pointer">2nd</th>
                    <th @click.prevent="sortOn('max_students')" class="cursor-pointer">Max Students</th>
                    <th @click.prevent="sortOn('students_count')" class="cursor-pointer">Students Applied</th>
                    <th @click.prevent="sortOn('accepted_students_count')" class="cursor-pointer">Accepted</th>
                    <th>Students</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="project in projects" :key="project.id">
                    <td>
                    <a
                        :href="getProjectUrl(project.id)"
                        :class="{ 'has-text-grey-light': !project.is_active }"
                        :title="project.is_active ? '' : 'Inactive'"
                    >
                        @{{ project.title }}
                    </a>
                        <span v-if="project.is_confidential" class="icon is-small" title="Confidential">
                            <i>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M4 8V6a6 6 0 1 1 12 0v2h1a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-8c0-1.1.9-2 2-2h1zm5 6.73V17h2v-2.27a2 2 0 1 0-2 0zM7 6v2h6V6a3 3 0 0 0-6 0z"/></svg>
                            </i>
                        </span>
                        <span v-if="project.is_placement" class="icon is-small" title="Placement">
                            <i>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 20S3 10.87 3 7a7 7 0 1 1 14 0c0 3.87-7 13-7 13zm0-11a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg>
                            </i>
                        </span>
                    </td>
                    <td>@{{ project.owner.full_name }}</td>
                    <td>@{{ project.second_supervisor ? project.second_supervisor.full_name : '' }}</td>
                    <td>@{{ project.max_students }}</td>
                    <td>@{{ project.students_count }}</td>
                    <td>@{{ project.accepted_students_count }}</td>
                    <td>
                        <span v-for="student in project.students">
                            <span class="tag" :class="{ 'is-success': student.pivot.is_accepted }">@{{ student.pivot.choice }}</span>
                            @{{ student.full_name }}<br>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </span>
</filterable-items>

@endsection
