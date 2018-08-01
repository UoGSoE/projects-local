<template>
    <div class="field has-addons">
        <div class="control is-expanded">
            <input class="input is-fullwidth" type="text" v-model="title" @keyup.enter="update">
        </div>
        <div class="control">
            <button class="button is-info" :class="{'is-loading': saving}" @click="update">
                Update
            </button>
        </div>
        <div class="control">
            <button class="button is-danger" :class="{'is-loading': deleting}" @click="destroy">
                {{ deleteText }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
  props: ["area"],

  data() {
    return {
      title: this.area.title,
      saving: false,
      deleting: false,
      confirmDelete: false
    };
  },

  computed: {
    deleteText() {
      if (this.confirmDelete) {
        return "Really?";
      }
      return "Delete";
    }
  },

  methods: {
    update() {
      this.saving = true;
      axios
        .post(route("researcharea.update", this.area.id), {
          title: this.title
        })
        .takeAtLeast(200)
        .then(response => {
          this.saving = false;
        })
        .catch(error => {
          this.saving = false;
          console.log(error);
        });
    },

    destroy() {
      if (!this.confirmDelete) {
        this.confirmDelete = true;
        return;
      }
      this.deleting = true;
      axios
        .delete(route("researcharea.destroy", this.area.id))
        .takeAtLeast(200)
        .then(response => {
          this.$emit("destroy", this.area.id);
        })
        .catch(error => {
          console.log(error);
        });
    }
  }
};
</script>
