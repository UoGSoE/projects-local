
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

const app = new Vue({
    el: '#app',

    data: {
        showConfirmation: false,
        openProjects: [],
        selectedStudent: null,
        requiredChoices: window.config.required_choices,
        choices: {
            first: null,
            second: null,
            third: null,
            fourth: null,
            fifth: null
        }
    },

    computed: {
        anyProjectsChosen() {
            return this.choices.first || this.choices.second || this.choices.third || this.choices.fourth || this.choices.fifth;
        },
        numberChosen() {
            var total = 0;
            for (var key in this.choices) {
                if (this.choices.hasOwnProperty(key)) {
                    if (this.choices[key] != null) {
                        total++;
                    }
                }
            }
            return total;
        }
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

        isExpanded: function (projectId) {
            if (this.openProjects.indexOf(projectId) != -1) {
                return true;
            }
            return false;
        },

        expandProject: function (projectId) {
            if (this.isExpanded(projectId)) {
                let index = this.openProjects.indexOf(projectId);
                this.openProjects.splice(index, 1);
                return;
            }
            this.openProjects.push(projectId);
        },

        isChosen: function (projectId) {
            if (this.choices.first == projectId) {
                return true;
            }
            if (this.choices.second == projectId) {
                return true;
            }
            if (this.choices.third == projectId) {
                return true;
            }
            if (this.choices.fourth == projectId) {
                return true;
            }
            if (this.choices.fifth == projectId) {
                return true;
            }
        },

        choose: function (choice, projectId) {
            let keys = ['first', 'second', 'third', 'fourth', 'fifth'];
            keys.forEach(key => {
                if (this.choices[key] == projectId) {
                    this.choices[key] = null;
                }
            });
            this.choices[choice] = projectId;
        },

        submitChoices() {
            var choices = {
                "1": this.choices.first,
                "2": this.choices.second,
                "3": this.choices.third,
                "4": this.choices.fourth,
                "5": this.choices.fifth,
            };
            console.log(choices);
            axios.post('/choices', {choices: choices})
                 .then(response => {
                    window.location = '/thank-you';
                 })
                 .catch(error => {
                    console.log(error);
                 });
        }
    }
});
