<template>
    <div>
        <h4 class="title is-4">Student Applications</h4>
        <form method="POST" action="">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Choice</th>
                        <th>Accepted?</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="student in students">
                        <td>
                            <span v-if="user.isAdmin">
                                <a href="route('admin.user.show', student.id)">
                                    {{ student.full_name }}
                                </a>
                            </span>
                            <span v-else>
                                {{ student.full_name }}
                            </span>
                            <span
                                v-if="student.profile"
                                @click='$emit("showprofile", student)'
                                role="button"
                                style="cursor: pointer;"
                                title="Show students profile"
                            >
                                <span class="icon" style="width: 1em;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5 5a5 5 0 0 1 10 0v2A5 5 0 0 1 5 7V5zM0 16.68A19.9 19.9 0 0 1 10 14c3.64 0 7.06.97 10 2.68V20H0v-3.32z"/></svg>
                                </span>
                            </span>
                        </td>
                        <td>
                            {{ student.choice }}
                        </td>
                        <td>
                            <label v-if="canAcceptStudent(student)">
                                <input
                                    type="checkbox"
                                    v-model="acceptedStudents"
                                    :value="student.id"
                                >
                            </label>
                            <label v-else>
                                {{ student.is_accepted ? 'Yes' : 'No' }}
                            </label>
                        </td>
                    </tr>
                </tbody>
            </table>
            <button
                v-if="changesHaveBeenMade"
                class="button"
                name="accept"
                @click.prevent="submit"
            >
                Save Changes
            </button>
        </form>
    </div>
</template>

<script>
    export default {
        props: ['students', 'project'],

        mounted() {
            this.students.forEach(student => {
                if (student.is_accepted) {
                    console.log(student.id);
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
            canAcceptStudent(student) {
                // admins can do anything
                if (this.user.isAdmin) {
                    return true;
                }
                // staff cannot choose anything for postgrad projects
                if (this.project.category == 'postgrad') {
                    return false;
                }
                // if the student is already accepted, staff cannot change it
                if (student.is_accepted) {
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
                axios.post('/project/' + this.project.id + '/accept-students', {
                    'students': this.acceptedStudents
                }).then(response => {
                    console.log('Woo');
                }).catch(error => {
                    console.log('Boo');
                });
            },
        },

    }
</script>
