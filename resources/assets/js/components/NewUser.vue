<template>
    <div>
        <div @click="mode = 'input'" style="position:absolute; top: 0px;">
            <transition name="fadeWidth" mode="out-in">
            <span v-if="mode === 'button'" key="button" class="button">+ New User</span>
            <span v-else key="input">
                <div class="field has-addons">
                  <div class="control">
                    <input class="input" type="text" v-model="username" placeholder="Enter a GUID..." autofocus="autofocus">
                  </div>
                  <div class="control">
                    <a class="button" :class="mainButtonClassList" @click.prevent="findUser">
                      Search
                    </a>
                  </div>
                  <div v-show="errorMessage" class="control">
                    <a class="button is-danger" disabled>
                      {{ errorMessage }}
                    </a>
                  </div>
                </div>
            </span>
            </transition>
            <div v-if="user" class="box" style="position: relative !important; z-index: 5;">
                <dl>
                    <dt class="has-text-weight-semibold">Email</dt>
                    <dd>{{ user.email }}</dd>
                    <dt class="has-text-weight-semibold">Surname</dt>
                    <dd>{{ user.surname }}</dd>
                    <dt class="has-text-weight-semibold">Forenames</dt>
                    <dd>{{ user.forenames }}</dd>
                    <hr />
                    <button class="button is-info" @click="saveUser" :class="{ 'is-loading': saving }">Add User</button>
                </dl>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: [],

        mounted() {
        },

        data() {
            return {
                mode: 'button',
                errorMessage: '',
                username: '',
                user: null,
                searching: false,
                saving: false,
            };
        },

        computed: {
            mainButtonClassList() {
                if (this.searching) {
                    return 'is-info is-loading';
                }
                if (! this.user) {
                    return 'is-info';
                }
                return '';
            }
        },

        methods: {
            findUser() {
                console.log(this.username);
                this.searching = true;
                axios.post(route('api.user.find', {guid: this.username}))
                    .then(response => {
                        this.user = response.data.data.user;
                        this.searching = false;
                        this.errorMessage = '';
                    })
                    .catch(error => {
                        this.searching = false;
                        if (error.response.data.message) {
                            this.errorMessage = error.response.data.message;
                        } else if (error.response.statusText) {
                            this.errorMessage = error.response.statusText;
                        } else {
                            this.errorMessage = error.message;
                        }
                    });
            },

            saveUser() {
                this.saving = true;
                axios.post(route('api.user.store', {guid: this.username}))
                    .then(response => {
                        this.user = null;
                        console.log(response);
                        this.saving = false;
                        location.reload();
                    })
                    .catch(error => {
                        this.saving = false;
                        this.user = null;
                        if (error.response.data.message) {
                            this.errorMessage = error.response.data.message;
                        } else if (error.response.statusText) {
                            this.errorMessage = error.response.statusText;
                        } else {
                            this.errorMessage = error.message;
                        }
                    });
            }
        },
    }
</script>
