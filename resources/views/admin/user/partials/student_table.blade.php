<filterable-items :items='@json($users)'>
    <span  slot-scope="{ items: students, inputAttrs, inputEvents, sortOn }">
        <input class="input" type="text" v-bind="inputAttrs" v-on="inputEvents" placeholder="Filter table..." autofocus>
        <table class="table is-fullwidth is-striped is-hover">
            <thead>
                <tr>
                    <th @click.prevent="sortOn('username')" class="cursor-pointer">User</th>
                    <th @click.prevent="sortOn('surname')" class="cursor-pointer">Surname</th>
                    <th @click.prevent="sortOn('forenames')" class="cursor-pointer">Forenames</th>
                    <th @click.prevent="sortOn('email')" class="cursor-pointer">Email</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="student in students" :key="student.id">
                    <td>
                        <a :href="showUserUrl(student.id)">@{{ student.username }}</a>
                    </td>
                    <td>@{{ student.surname }}</td>
                    <td>@{{ student.forenames }}</td>
                    <td><a :href="`mailto:${student.email}`">@{{ student.email }}</a></td>
                </tr>
            </tbody>
        </table>
    </span>
</filterable-items>
