<template>
    <div>
        <label class="label">
            Manually Allocate a Student
        </label>
        <div class="field has-addons">
            <div class="control">
                <div class="select">
                    <select v-model="student_id" name="student_id">
                        <option v-for="student in students" :key="student.id" :value="student.id">{{ student.full_name }} {{ student.username }}</option>
                    </select>
                </div>
            </div>
            <div class="control">
                <button class="button" @click="submit">Allocate &amp; Accept</button>
            </div>
        </div>
            <span v-if="errorMessage" class="has-text-danger">
                {{ errorMessage }}
            </span>
            <span v-if="fullyAllocated">
                 <span class="tag is-danger">Warning!</span>
                 <span class="has-text-danger">Maximum number for this project is {{ project.max_students }}</span>
            </span>
    </div>
</template>

<script>
export default {
  props: ["students", "project"],

  mounted() {},

  data() {
    return {
      student_id: null,
      errorMessage: ""
    };
  },

  computed: {
    fullyAllocated() {
      return this.project.accepted_students_count >= this.project.max_students;
    }
  },

  methods: {
    submit() {
      axios
        .post(route("admin.project.add_student", this.project.id), {
          student_id: this.student_id
        })
        .then(response => {
          location.reload();
        })
        .catch(error => {
          console.log(error.response);
          this.errorMessage = error.response.statusText;
        });
    }
  }
};
</script>