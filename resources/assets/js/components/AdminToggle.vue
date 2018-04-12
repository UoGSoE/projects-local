<template>
    <div>
        <span role="button" style="cursor: pointer; margin-right: 1em;" @click="toggleAdmin" class="icon" :class="getIconClass" :title="getIconTitle" :id="'admintoggle-' + id">
            <svg style="fill: currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5 5a5 5 0 0 1 10 0v2A5 5 0 0 1 5 7V5zM0 16.68A19.9 19.9 0 0 1 10 14c3.64 0 7.06.97 10 2.68V20H0v-3.32z"/></svg>
        </span>
    </div>
</template>

<script>
    export default {
        props: ['user'],

        data() {
            return {
                id: this.user.id,
                isAdmin: this.user.isAdmin,
                errorMessage: '',
            }
        },

        computed: {
            getIconClass() {
                if (this.errorMessage) {
                    return 'has-text-danger';
                }
                return this.isAdmin ? 'has-text-success' : 'has-text-grey-light';
            },
            getIconTitle() {
                return this.errorMessage ? this.errorMessage : 'Admin?';
            }
        },

        methods: {
            toggleAdmin() {
                axios.post(route('admin.users.toggle_admin', this.id))
                     .then(response => {
                        this.isAdmin = ! this.isAdmin;
                        this.errorMessage = '';
                     })
                     .catch(error => {
                        this.errorMessage = error.response.data.message ? error.response.data.message : error.message;
                     });
            }
        }
    }
</script>
