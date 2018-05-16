<template>
    <div>
        <div @click.prevent="showInput">
            <transition name="fadeWidth" mode="out-in">
            <span v-if="mode === 'button'">
                <a :href="'mailto:' + email">{{ email }}</a>
                <span key="button" class="button is-small">Change</span>
            </span>
            <span v-else key="input">
                <form>
                    <div class="field has-addons">
                        <div class="control">
                            <input class="input" type="text" v-model="email">
                        </div>
                        <div class="control">
                            <button class="button" @click.prevent="updateEmail">
                                Save
                            </button>
                        </div>
                        <div v-show="errorMessage" class="control">
                            <a class="button is-danger" disabled>
                            {{ errorMessage }}
                            </a>
                        </div>
                    </div>
                </form>
            </span>
            </transition>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['user'],

        mounted() {
        },

        data() {
            return {
                mode: 'button',
                errorMessage: '',
                email: this.user.email,
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
            showInput() {
                this.mode = 'input';
            },

            updateEmail() {
                this.saving = true;
                axios.post(route('api.user.update', this.user.id), {'email': this.email})
                    .then(response => {
                        this.saving = false;
                        location.reload();
                    })
                    .catch(error => {
                        this.saving = false;
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
