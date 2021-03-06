<template>
  <div>
    <div v-if="students.length <= 0">
      <article class="message">
        <div class="message-body">No students have applied yet</div>
      </article>
    </div>
    <div v-else>
      <h3 class="title is-3">Student Applications</h3>
      <form method="POST" action id="student-list-form">
        <table class="table">
          <thead>
            <tr>
              <th>Student</th>
              <th>Choice</th>
              <th>Accepted?</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in students" :key="student.id">
              <td>
                <span v-if="user.isAdmin">
                  <a :href="showUserUrl(student.id)">{{ student.full_name }}</a>
                </span>
                <span v-else>{{ student.full_name }}</span>
              </td>
              <td>{{ student.choice }}</td>
              <td :id="'status-' + student.id">
                <label v-if="canAcceptStudent(student)">
                  <input
                    :id="'accept-' + student.id"
                    :name="'accept-' + student.id"
                    type="checkbox"
                    v-model="acceptedStudents"
                    :value="student.id"
                  >
                </label>
                <label v-else>{{ student.is_accepted ? 'Yes' : 'No' }}</label>
              </td>
            </tr>
          </tbody>
        </table>
        <button
          v-if="changesHaveBeenMade"
          class="button"
          name="accept"
          @click.prevent="submit"
        >Save Changes</button>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  props: ["students", "project", "studentList"],

  mounted() {
    this.students.forEach(student => {
      if (student.is_accepted) {
        this.acceptedStudents.push(student.id);
        this.initiallyAccepted.push(student.id);
      }
    });
  },

  data() {
    return {
      acceptedStudents: [],
      initiallyAccepted: [],
      user: window.user,
      manual_student_id: null
    };
  },

  computed: {
    changesHaveBeenMade() {
      // if the dynamic 'acceptedStudents' array is different length to the initial one, then yes
      if (this.initiallyAccepted.length != this.acceptedStudents.length) {
        return true;
      }

      // if there's anything in the dynamic acceptedStudents array that doesn't exist in the initial one, then yes
      var changed = false;
      this.acceptedStudents.forEach(studentId => {
        if (this.initiallyAccepted.indexOf(studentId) == -1) {
          changed = true;
        }
      });
      if (changed) {
        return true;
      }

      // if there's anything in the initial list that isn't in the dynamic one, then yes
      this.initiallyAccepted.forEach(studentId => {
        if (this.acceptedStudents.indexOf(studentId) == -1) {
          changed = true;
        }
      });
      return changed;
    }
  },

  methods: {
    showUserUrl: function(userId) {
      return route("admin.user.show", userId);
    },

    canAcceptStudent(student) {
      // admins can do anything
      if (!!+this.user.isAdmin) {
        return true;
      }
      // staff cannot choose anything unless the teaching office have flagged it
      if (!this.project.staff_can_accept) {
        return false;
      }
      // if the student is already accepted, staff cannot change it
      if (!!+student.is_accepted) {
        return false;
      }
      // can only accept students who have made this project their first choice
      if (student.choice != 1) {
        return false;
      }
      // ~~ end of byzantine rules ~~
      return true;
    },

    submit() {
      axios
        .post(route("project.accept_students", this.project.id), {
          students: this.acceptedStudents
        })
        .then(response => {
          location.reload();
        })
        .catch(error => {
          console.log("Boo");
        });
    }
  }
};
</script>
