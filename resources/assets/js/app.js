
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
Vue.component('new-user', require('./components/NewUser.vue'));
Vue.component('email-edit', require('./components/EmailEdit.vue'));
Vue.component('manual-student-allocator', require('./components/ManualStudentAllocator.vue'));
import { TableComponent, TableColumn } from 'vue-table-component';
Vue.component('table-component', TableComponent);
Vue.component('table-column', TableColumn);


window.moment = require('moment');
import Pikaday from "pikaday";
import "pikaday/css/pikaday.css";
Vue.directive("pikaday", {
    bind: (el, binding) => {
        el.pikadayInstance = new Pikaday({
            field: el,
            format: 'DD/MM/YYYY',
            onSelect: () => {
                var event = new Event("input", { bubbles: true });
                el.value = el.pikadayInstance.toString();
                el.dispatchEvent(event);
            }
            // add more Pikaday options below if you need
            // all available options are listed on https://github.com/dbushell/Pikaday
        });
    },

    unbind: el => {
        el.pikadayInstance.destroy();
    }
});

const app = new Vue({
    el: '#app',

    data: {
        showConfirmation: false,
        openProjects: [],
        selectedStudent: null,
    },

    methods: {
        showUserUrl: function (userId) {
            return route('admin.user.show', userId);
        },

        getProjectUrl: function (projectId) {
            return route('project.show', projectId);
        },

        editProgrammeUrl: function (programmeId) {
            return route('admin.programme.edit', programmeId);
        },

        deleteProject: function (projectId) {
            console.log(projectId);
            this.showConfirmation = false;
            axios.delete(route('project.delete', projectId))
                .then(function (response) {
                    window.location = route('home');
                });
        },

        deleteCourse: function (courseId) {
            console.log(courseId);
            this.showConfirmation = false;
            axios.delete(route('admin.course.destroy', courseId))
                .then(function (response) {
                    window.location = route('admin.course.index');
                });
        },

        deleteProgramme: function (programmeId) {
            console.log(programmeId);
            this.showConfirmation = false;
            axios.delete(route('admin.programme.destroy', programmeId))
                .then(function (response) {
                    window.location = route('admin.programme.index');
                });
        },

        deleteCourseStudents: function (courseId) {
            console.log(courseId);

            this.showConfirmation = false;
            axios.delete(route('course.remove_students', courseId))
                .then(function (response) {
                    window.location = route('admin.course.show', courseId);
                });
        },

        deleteStudents: function (category) {
            console.log(category);

            this.showConfirmation = false;
            axios.delete(route('students.remove_' + category))
                .then(function (response) {
                    window.location = route('admin.users', category);
                });
        },

        deleteUser: function (userId) {
            console.log(userId);

            this.showConfirmation = false;
            axios.delete(route('admin.user.delete', userId))
                .then(function (response) {
                    window.location = route('home');
                });
        }
    }
});
