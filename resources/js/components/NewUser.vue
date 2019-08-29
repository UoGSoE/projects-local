<template>
  <div>
    <div @click.prevent="showInput" style="position:absolute; top: 0px;">
      <transition name="fadeWidth" mode="out-in">
        <span v-if="mode === 'button'" key="button" class="button">+ New User</span>
        <span v-else key="input">
          <form>
            <div class="field has-addons">
              <div class="control">
                <input
                  class="input"
                  type="text"
                  v-model="username"
                  placeholder="Enter a GUID..."
                  ref="search"
                  autofocus
                >
              </div>
              <div class="control">
                <button class="button" :class="mainButtonClassList" @click.prevent="findUser">Search</button>
              </div>
              <div v-show="errorMessage" class="control">
                <a class="button is-danger" disabled>{{ errorMessage }}</a>
              </div>
            </div>
          </form>
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
          <span v-if="looksLikeMatric(username)">
            <dt class="has-text-weight-semibold">Course</dt>
            <dd>
              <div class="field">
                <div class="control">
                  <input
                    class="input"
                    type="text"
                    v-model="user.course"
                    placeholder="Enter a course code (eg, ENG1234)"
                  >
                </div>
              </div>
            </dd>
          </span>
          <hr>
          <button
            class="button is-info"
            @click="saveUser"
            :class="{ 'is-loading': saving }"
          >Add User</button>
        </dl>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      mode: "button",
      errorMessage: "",
      username: "",
      user: null,
      searching: false,
      saving: false
    };
  },

  computed: {
    mainButtonClassList() {
      if (this.searching) {
        return "is-info is-loading";
      }
      if (!this.user) {
        return "is-info";
      }
      return "";
    }
  },

  methods: {
    looksLikeMatric(username) {
      return /^[0-9]/.test(username);
    },
    showInput() {
      this.mode = "input";
      //   this.$nextTick(this.$refs.search.focus());
    },

    findUser() {
      console.log(this.username);
      this.searching = true;
      axios
        .post(route("api.user.find", { guid: this.username }))
        .then(response => {
          this.user = response.data.data.user;
          this.user.course = "";
          this.searching = false;
          this.errorMessage = "";
        })
        .catch(error => {
          this.user = null;
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
      axios
        .post(
          route("api.user.store", {
            guid: this.username,
            course: this.user.course
          })
        )
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
  }
};
</script>
