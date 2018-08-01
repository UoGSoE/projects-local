<template>
    <div>
        <label class="label">Add new research area</label>
        <div class="field has-addons">
            <div class="control">
                <input class="input" type="text" v-model="title">
            </div>
            <div class="control">
                <button class="button is-info" :class="{'is-loading': busy}" @click="add">
                    Add
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
  data() {
    return {
      title: "",
      busy: false
    };
  },

  methods: {
    add() {
      this.busy = true;
      axios
        .post(route("researcharea.store"), {
          title: this.title
        })
        .takeAtLeast(300)
        .then(response => {
          window.location.reload(true);
        })
        .catch(error => {
          console.log(error);
        });
    }
  }
};
</script>
