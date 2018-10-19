<template>
    <div>
        <span role="button" class="button" style="cursor: pointer; margin-right: 1em;" @click="toggleAdmin" :id="'admintoggle-' + id">
            {{ buttonText }}
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
            buttonText() {
                return (this.isAdmin ? 'Remove Admin Rights' : 'Give Admin Rights');
            }
        },

        methods: {
            toggleAdmin() {
                axios.post(route('admin.users.toggle_admin', this.id))
                     .then(response => {
                        this.errorMessage = '';
                        location.reload();
                     })
                     .catch(error => {
                        this.errorMessage = error.response.data.message ? error.response.data.message : error.message;
                     });
            }
        }
    }
</script>
