
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
    }
});
