<template>
    <div>
        <input class="checkbox" type="checkbox" v-model="isAdmin" @change="toggleAdmin">
    </div>
</template>

<script>
    export default {
        props: ['user'],

        data() {
            return {
                id: this.user.id,
                isAdmin: this.user.isAdmin,
            }
        },

        methods: {
            toggleAdmin() {
                axios.post('/admin/user/' + this.id + '/toggle-admin')
                     .then(response => {
                        this.isAdmin = ! this.isAdmin;
                     })
                     .catch(error => {
                        console.log(error);
                     });
            }
        }
    }
</script>
