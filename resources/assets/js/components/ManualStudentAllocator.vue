<template>
    <div>
        <label class="label">Manually Allocate a Student</label>
        <div class="field has-addons">
            <div class="control">
                <div class="select">
                    <select v-model="student_id" name="student_id">
                        <option v-for="student in students" :value="student.id">{{ student.full_name }} {{ student.username }}</option>
                    </select>
                </div>
            </div>
            <div class="control">
                <button class="button" @click="submit">Allocate &amp; Accept</button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['students', 'project'],

        mounted() {
        },

        data() {
            return {
                student_id: null,
            };
        },

        computed: {
        },

        methods: {
            submit() {
                axios.post(route('admin.project.add_student', this.project.id), {
                    'student_id': this.student_id
                }).then(response => {
                    console.log('Woo');
                    location.reload();
                }).catch(error => {
                    console.log('Boo');
                });
            },
        },
    }
</script>