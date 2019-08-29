<filterable-items :items='@json($users)'>
    <span  slot-scope="{ items: staffMembers, inputAttrs, inputEvents, sortOn }">
        <input class="input" type="text" v-bind="inputAttrs" v-on="inputEvents" placeholder="Filter table..." autofocus>
        <br><br>
        <table class="table is-bordered is-fullwidth">
            <thead>
                <tr>
                    <th @click.prevent="sortOn('username')" class="cursor-pointer bottom-header">User</th>
                    <th @click.prevent="sortOn('surname')" class="cursor-pointer bottom-header">Surname</th>
                    <th @click.prevent="sortOn('forenames')" class="cursor-pointer bottom-header">Forenames</th>
                    <th @click.prevent="sortOn('ugrad_beng_active')" class="cursor-pointer bottom-header rotate-header"><span class="rotate-header-text">UGrad B.Eng Proj</span></th>
                    <th @click.prevent="sortOn('ugrad_meng_active')" class="cursor-pointer bottom-header rotate-header"><span class="rotate-header-text">UGrad M.Eng Proj</span></th>
                    <th @click.prevent="sortOn('ugrad_etc_active')" class="cursor-pointer bottom-header rotate-header"><span class="rotate-header-text">UGrad SIT/UESTC Proj</span></th>
                    <th @click.prevent="sortOn('pgrad_active')" class="cursor-pointer bottom-header rotate-header"><span class="rotate-header-text">PGrad Proj</span></th>
                    <th @click.prevent="sortOn('second_ugrad_beng_active')" class="cursor-pointer bottom-header rotate-header"><span class="rotate-header-text">2nd Ugrad B.Eng Proj</span></th>
                    <th @click.prevent="sortOn('second_ugrad_meng_active')" class="cursor-pointer bottom-header rotate-header"><span class="rotate-header-text">2nd Ugrad M.Eng Proj</span></th>
                    <th @click.prevent="sortOn('second_ugrad_etc_active')" class="cursor-pointer bottom-header rotate-header"><span class="rotate-header-text">2nd Ugrad SIT/UESTC Proj</span></th>
                    <th @click.prevent="sortOn('second_pgrad_active')" class="cursor-pointer bottom-header rotate-header"><span class="rotate-header-text">2nd Pgrad Proj</span></th>
                    <th @click.prevent="sortOn('email')" class="cursor-pointer bottom-header">Email</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="staff in staffMembers" :key="staff.id">
                    <td>
                        <a :href="showUserUrl(staff.id)">@{{ staff.username }}</a>
                    </td>
                    <td>@{{ staff.surname }}</td>
                    <td>@{{ staff.forenames }}</td>
                    <td>@{{ staff.ugrad_beng_active }} / @{{ staff.ugrad_beng_allocated}}</td>
                    <td>@{{ staff.ugrad_meng_active }} / @{{ staff.ugrad_meng_allocated}}</td>
                    <td>@{{ staff.ugrad_etc_active }} / @{{ staff.ugrad_etc_allocated}}</td>
                    <td>@{{ staff.pgrad_active }} / @{{ staff.pgrad_allocated}}</td>
                    <td>@{{ staff.second_ugrad_beng_active }} / @{{ staff.second_ugrad_beng_allocated}}</td>
                    <td>@{{ staff.second_ugrad_meng_active }} / @{{ staff.second_ugrad_meng_allocated}}</td>
                    <td>@{{ staff.second_ugrad_etc_active }} / @{{ staff.second_ugrad_etc_allocated}}</td>
                    <td>@{{ staff.second_pgrad_active }} / @{{ staff.second_pgrad_allocated}}</td>
                    <td><a :href="`mailto:${staff.email}`">@{{ staff.email }}</a></td>
                </tr>
            </tbody>
        </table>
    </span>
</filterable-items>
