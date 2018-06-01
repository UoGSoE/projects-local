<template>
    <div>
        <ul>
			<li class="columns" v-for="student in studentList" :key="student.id">
				<span class="column" style="padding-bottom: 3px; padding-top: 3px;">
					<a :href="showUserUrl(student.id)">
						{{ student.full_name }} ({{ student.matric }})
					</a>
				</span>
				<span class="column" style="padding-bottom: 3px; padding-top: 3px;">
                    <button class="button is-text has-text-danger is-small" @click="confirmRemoveStudent(student)">
                        <transition name="fade" mode="out-in">
                            <span :key="student.should_remove">
                                {{ student.should_remove ? 'Really Remove?' : 'Remove' }}
                            </span>
                        </transition>
                    </button>
				</span>
			</li>
		</ul>
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
      if (student.should_remove) {
        this.removeStudent(student);
        return;
      }
      student.should_remove = true;
      let index = this.studentList.findIndex(
        existingStudent => existingStudent.id == student.id
      );
      this.studentList.splice(index, 1, student);
    },
    removeStudent(student) {
      axios
        .delete(route("admin.user.delete", student.id))
        .then(response => {
          let index = this.studentList.findIndex(
            existingStudent => existingStudent.id == student.id
          );
          this.studentList.splice(index, 1);
        })
        .catch(error => {
          console.log(error);
        });
    }
  }
};
</script>