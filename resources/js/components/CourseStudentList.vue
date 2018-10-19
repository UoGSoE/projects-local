<template>
    <div>
        <deletable-list v-model="studentList" :delete-callback="removeStudent">
            <ul slot-scope="{ items, removeItem, clear }">
                <li class="columns" v-for="student in items" :key="student.id">
                    <span class="column" style="padding-bottom: 3px; padding-top: 3px;">
                        <a :href="showUserUrl(student.id)">
                            {{ student.full_name }} ({{ student.matric }})
                        </a>
                    </span>
                    <span class="column" style="padding-bottom: 3px; padding-top: 3px;">
                        <transition name="fade" mode="out-in">
                            <button
                              :id="'remove-student-' + student.id"
                              :key="student.should_remove"
                              class="button is-text has-text-danger is-small"
                              @click="student.should_remove ? removeItem(student) : confirmRemoveStudent(student)"
                            >
                                {{ student.should_remove ? 'Really Remove?' : 'Remove' }}
                            </button>
                        </transition>
                    </span>
                </li>
            </ul>
        </deletable-list>
    </div>
</template>
<script>
export default {
  props: ["students"],
  data() {
    return {
      studentList: this.students
    };
  },
  methods: {
    showUserUrl: function(userId) {
      return route("admin.user.show", userId);
    },
    confirmRemoveStudent(student) {
      student.should_remove = true;
      let index = this.studentList.findIndex(
        existingStudent => existingStudent.id == student.id
      );
      this.studentList.splice(index, 1, student);
    },
    removeStudent(student) {
      axios
        .delete(route("admin.user.delete", student.id))
        .then(response => {})
        .catch(error => {
          console.log(error);
        });
    }
  }
};
</script>