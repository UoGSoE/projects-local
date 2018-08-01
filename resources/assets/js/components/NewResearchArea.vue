<template>
    <div>
        <div class="field has-addons">
            <div class="control">
                <input class="input" type="text" v-model="title" @keyup.enter="add" placeholder="Add new area..." autofocus>
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
        .takeAtLeast(200)
        .then(response => {
          this.$emit("add", response.data);
          this.busy = false;
          this.title = "";
        })
        .catch(error => {
          console.log(error);
        });
    }
  }
};
</script>
