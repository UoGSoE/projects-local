<template>
    <div>
        <ul>
			<li class="columns" v-for="student in studentList">
				<span class="column" style="padding-bottom: 3px; padding-top: 3px;">
					<a :href="showUserUrl(student.id)">
						{{ student.full_name }} ({{ student.matric }})
					</a>
				</span>
				<span class="column" style="padding-bottom: 3px; padding-top: 3px;">
                    <button class="button is-text has-text-danger is-small" @click="confirmRemoveStudent(student)">
                        Remove
                    </button>
				</span>
			</li>
		</ul>
        <confirmation-dialog :show="showConfirmation" @cancel="showConfirmation = false" @confirm="removeStudent">
            Do you really want to delete {{ selectedStudent.full_name }}?
        </confirmation-dialog>

    </div>
</template>
<script>
export default {
    props: ['students'],
    data() {
        return {
            studentList: this.students,
            selectedStudent: {},
            showConfirmation: false,
        }
    },
    methods: {
        showUserUrl: function (userId) {
            return route('admin.user.show', userId);
        },
        confirmRemoveStudent(studentId) {
            this.selectedStudent = studentId;
            this.showConfirmation = true;
        },
        removeStudent() {
            axios.delete(route('admin.user.delete', this.selectedStudent.id))
                .then(response => {
                    let index = this.studentList.findIndex(student => student.id == this.selectedStudent.id);
                    this.studentList.splice(index, 1);
                    this.showConfirmation = false;
                    this.selectedStudent = {};
                })
                .catch(error => {
                    console.log(error);
                });
        }
    }
}
</script>