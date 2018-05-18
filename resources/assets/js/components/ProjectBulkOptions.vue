<template>
    <div>
<table-component
    :data='projects'
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
             :href="row.id"
             :class="{ 'has-text-grey-light': !row.is_active }"
             :title="row.is_active ? '' : 'Inactive'"
            >
             {{ row.title }}
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
    <table-column show="rand()" label="Active?">
        <template slot-scope="row">
            <input type="checkbox" class="checkbox" :id="'active' + row.id" @click="toggleActive(row.id)" :checked="isActive(row.id)">
        </template>
    </table-column>
    <table-column show="row.id" label="Delete?">
        <template slot-scope="row">
            <input type="checkbox" class="checkbox is-danger" :id="'delete' + row.id" v-model="deletes" :value="row.id">
        </template>
    </table-column>
    <table-column show="" :hidden="true" filter-on="course_codes"></table-column>
</table-component>

<hr />

<div class="field is-pulled-right">
    <div class="control">
        <button type="button" class="button" @click="submit">Save Changes</button>
    </div>
</div>

<confirmation-dialog :show="showConfirmation" @cancel="showConfirmation = false" @confirm="reallySubmit">
    Do you really want to delete {{ numberToDelete }} projects?
</confirmation-dialog>

    </div>
</template>

<script>

export default {
    props: ['projects'],
    data() {
        return {
            actives: [],
            deletes: [],
            showConfirmation: false,
        };
    },
    computed: {
        numberToDelete() {
            return this.deletes.length;
        }
    },
    created() {
        this.projects.forEach(project => {
            this.actives.push({
                id: project.id,
                is_active: project.is_active,
            });
        });
    },
    methods: {
        submit() {
            if (this.numberToDelete > 0) {
                this.showConfirmation = true;
                return;
            } else {
                this.reallySubmit();
            }
        },
        reallySubmit() {
            axios.post(route('admin.project.bulk-options.update', 'undergrad'), {
                active: this.actives,
                delete: this.deletes,
            }).then(response => {
                location.reload();
            }).catch(error => {
                console.log('Boo');
            });
        },
        toggleActive(id) {
            let index = this.actives.findIndex(project => project.id == id);
            this.actives[index].is_active = !this.actives[index].is_active;
        },
        isActive(id) {
            let index = this.actives.findIndex(project => project.id == id);
            return this.actives[index].is_active;
        }
    }
}
</script>