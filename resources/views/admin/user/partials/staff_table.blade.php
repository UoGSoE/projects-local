<filterable-items :items='@json($users)'>
    <span  slot-scope="{ items: staffMembers, inputAttrs, inputEvents, sortOn }">
        <input class="input" type="text" v-bind="inputAttrs" v-on="inputEvents" placeholder="Filter table..." autofocus>
        <table class="table is-fullwidth is-striped is-hover">
            <thead>
                <tr>
                    <th @click.prevent="sortOn('username')" class="cursor-pointer">User</th>
                    <th @click.prevent="sortOn('surname')" class="cursor-pointer">Surname</th>
                    <th @click.prevent="sortOn('forenames')" class="cursor-pointer">Forenames</th>
                    <th @click.prevent="sortOn('ugrad_active')" class="cursor-pointer">Ugrad Proj</th>
                    <th @click.prevent="sortOn('pgrad_active')" class="cursor-pointer">Pgrad Proj</th>
                    <th @click.prevent="sortOn('second_ugrad_active')" class="cursor-pointer">2nd Ugrad Proj</th>
                    <th @click.prevent="sortOn('second_pgrad_active')" class="cursor-pointer">2nd Pgrad Proj</th>
                    <th @click.prevent="sortOn('email')" class="cursor-pointer">Email</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="staff in staffMembers" :key="staff.id">
                    <td>
                        <a :href="showUserUrl(staff.id)">@{{ staff.username }}</a>
                    </td>
                    <td>@{{ staff.surname }}</td>
                    <td>@{{ staff.forenames }}</td>
                    <td>@{{ staff.ugrad_active }} / @{{ staff.ugrad_allocated}}</td>
                    <td>@{{ staff.pgrad_active }} / @{{ staff.pgrad_allocated}}</td>
                    <td>@{{ staff.second_ugrad_active }} / @{{ staff.second_ugrad_allocated}}</td>
                    <td>@{{ staff.second_pgrad_active }} / @{{ staff.second_pgrad_allocated}}</td>
                    <td><a :href="`mailto:${staff.email}`">@{{ staff.email }}</a></td>
                </tr>
            </tbody>
        </table>
    </span>
</filterable-items>
