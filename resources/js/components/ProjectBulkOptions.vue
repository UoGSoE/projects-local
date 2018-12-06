<template>
  <div>
    <filterable-items :items="projects">
      <span slot-scope="{ items: projects, inputAttrs, inputEvents, sortOn }">
        <input
          class="input"
          type="text"
          v-bind="inputAttrs"
          v-on="inputEvents"
          placeholder="Filter table..."
          autofocus
        >
        <table class="table is-fullwidth is-striped is-hover">
          <thead>
            <tr>
              <th @click.prevent="sortOn('title')" class="cursor-pointer">Title</th>
              <th @click.prevent="sortOn('owner_name')" class="cursor-pointer">Owner</th>
              <th class="cursor-pointer">2nd</th>
              <th class="cursor-pointer">Active?</th>
              <th class="cursor-pointer">Delete?</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="project in projects" :key="project.id">
              <td>
                <a
                  :href="getProjectUrl(project.id)"
                  :class="{ 'has-text-grey-light': !project.is_active }"
                  :title="project.is_active ? '' : 'Inactive'"
                >{{ project.title }}</a>
                <span v-if="project.is_confidential" class="icon is-small" title="Confidential">
                  <i>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                      <path
                        d="M4 8V6a6 6 0 1 1 12 0v2h1a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-8c0-1.1.9-2 2-2h1zm5 6.73V17h2v-2.27a2 2 0 1 0-2 0zM7 6v2h6V6a3 3 0 0 0-6 0z"
                      ></path>
                    </svg>
                  </i>
                </span>
                <span v-if="project.is_placement" class="icon is-small" title="Placement">
                  <i>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                      <path
                        d="M10 20S3 10.87 3 7a7 7 0 1 1 14 0c0 3.87-7 13-7 13zm0-11a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"
                      ></path>
                    </svg>
                  </i>
                </span>
              </td>
              <td>{{ project.owner.full_name }}</td>
              <td>{{ project.second_supervisor ? project.second_supervisor.full_name : '' }}</td>
              <td>
                <input
                  type="checkbox"
                  class="checkbox"
                  :id="'active' + project.id"
                  @click="toggleActive(project.id)"
                  :checked="isActive(project.id)"
                >
              </td>
              <td>
                <input
                  type="checkbox"
                  class="checkbox is-danger"
                  :id="'delete' + project.id"
                  v-model="deletes"
                  :value="project.id"
                >
              </td>
            </tr>
          </tbody>
        </table>
      </span>
    </filterable-items>

    <hr>

    <div class="field is-pulled-right">
      <div class="control">
        <button type="button" class="button" @click="submit">Save Changes</button>
      </div>
    </div>

    <confirmation-dialog
      :show="showConfirmation"
      @cancel="showConfirmation = false"
      @confirm="reallySubmit"
    >Do you really want to delete {{ numberToDelete }} projects?</confirmation-dialog>
  </div>
</template>

<script>
export default {
  props: ["projects"],
  data() {
    return {
      actives: [],
      deletes: [],
      showConfirmation: false
    };
  },
  computed: {
    numberToDelete() {
      return this.deletes.length;
    }
  },
  created() {
    this.projects.forEach(project => {
      this.actives.push({
        id: project.id,
        is_active: project.is_active
      });
    });
  },
  methods: {
    submit() {
      if (this.numberToDelete > 0) {
        this.showConfirmation = true;
        return;
      } else {
        this.reallySubmit();
      }
    },
    reallySubmit() {
      axios
        .post(route("admin.project.bulk-options.update", "undergrad"), {
          active: this.actives,
          delete: this.deletes
        })
        .then(response => {
          location.reload();
        })
        .catch(error => {
          console.log("Boo");
        });
    },
    toggleActive(id) {
      let index = this.actives.findIndex(project => project.id == id);
      this.actives[index].is_active = !this.actives[index].is_active;
    },
    isActive(id) {
      let index = this.actives.findIndex(project => project.id == id);
      return this.actives[index].is_active;
    },
    getProjectUrl: function(projectId) {
      return route("project.show", projectId);
    }
  }
};
</script>