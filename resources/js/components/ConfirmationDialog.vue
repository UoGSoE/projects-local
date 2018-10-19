<template>
    <div>
        <transition name="modal">
        <div class="modal" @click="cancel" v-if="show" :class="{ 'is-active': show} ">
            <div class="modal-background"></div>
            <div class="modal-card" @click.stop>
                <header class="modal-card-head">
                    <p class="modal-card-title">Please Confirm</p>
                    <button class="delete" aria-label="close" @click="cancel"></button>
                </header>
                <section class="modal-card-body">
                    <slot>Are you sure?</slot>
                </section>
                <footer class="modal-card-foot">
                    <button class="button is-danger" @click="confirm">Confirm</button>
                    <button class="button" @click="cancel">Cancel</button>
                </footer>
            </div>
        </div>
        </transition>
    </div>
</template>

<script>

module.exports = {

    props: ['show'],

    methods: {
        cancel() {
            this.$emit('cancel');
        },

        confirm() {
            this.$emit('confirm');
        }
    },

    mounted() {
        document.addEventListener("keydown", (e) => {
            if (this.show && e.keyCode == 27) {
                this.cancel();
            }
        });
    }
}

</script>
