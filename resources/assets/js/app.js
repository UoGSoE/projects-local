
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('confirmation-dialog', require('./components/ConfirmationDialog.vue'));
Vue.component('project-list', require('./components/ProjectList.vue'));
Vue.component('student-list', require('./components/StudentList.vue'));
Vue.component('admin-toggle', require('./components/AdminToggle.vue'));
import { TableComponent, TableColumn } from 'vue-table-component';
Vue.component('table-component', TableComponent);
Vue.component('table-column', TableColumn);

const app = new Vue({
    el: '#app',

    data: {
        showConfirmation: false,
        openProjects: [],
        selectedStudent: null,
    },

    methods: {
        deleteProject: function (projectId) {
            console.log(projectId);
            this.showConfirmation = false;
            axios.delete('/project/' + projectId)
                .then(function (response) {
                    window.location = '/';
                });
        },

        deleteCourse: function (courseId) {
            console.log(courseId);
            this.showConfirmation = false;
            axios.delete('/admin/course/' + courseId)
                .then(function (response) {
                    window.location = '/admin/course';
                });
        },

        deleteProgramme: function (programmeId) {
            console.log(programmeId);
            this.showConfirmation = false;
            axios.delete('/admin/programme/' + programmeId)
                .then(function (response) {
                    window.location = '/admin/programme';
                });
        },

        deleteCourseStudents: function (courseId) {
            console.log(courseId);

            this.showConfirmation = false;
            axios.delete('/admin/course/' + courseId + '/remove-students')
                .then(function (response) {
                    window.location = '/admin/course/' + courseId;
                });
        },

        deleteStudents: function (category) {
            console.log(category);

            this.showConfirmation = false;
            axios.delete('/admin/students/remove/' + category)
                .then(function (response) {
                    window.location = '/admin/users/' + category;
                });
        },

        deleteUser: function (userId) {
            console.log(userId);

            this.showConfirmation = false;
            axios.delete('/admin/user/' + userId)
                .then(function (response) {
                    window.location = '/';
                });
        }
    }
});
