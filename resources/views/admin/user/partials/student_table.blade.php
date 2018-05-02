
<table-component
    :data='@json($users)'
    sort-by="surname"
    sort-order="asc"
    table-class="table is-fullwidth is-striped is-hover"
    :show-caption="false"
    filter-input-class="input"
    >
    <table-column show="username" label="User">
        <template slot-scope="row">
            <a :href="showUserUrl(row.id)">@{{ row.username }}</a>
        </template>
    </table-column>
    <table-column show="surname" label="Surname"></table-column>
    <table-column show="forenames" label="Forename"></table-column>
    <table-column show="email" label="Email">
        <template slot-scope="row">
            <a :href="`mailto:${row.email}`">@{{ row.email }}</a>
        </template>
    </table-column>
</table-component>
