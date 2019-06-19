/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */


require('./bootstrap');

window.Vue = require('vue');

import Pikaday from 'pikaday';
import 'pikaday/css/pikaday.css';

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Creates a new promise that automatically resolves after some timeout:
Promise.delay = function (time) {
  return new Promise((resolve, reject) => {
    setTimeout(resolve, time);
  });
};

// Throttle this promise to resolve no faster than the specified time:
Promise.prototype.takeAtLeast = function (time) {
  return new Promise((resolve, reject) => {
    Promise.all([this, Promise.delay(time)]).then(([result]) => {
      resolve(result);
    }, reject);
  });
};

Vue.component('confirmation-dialog',require('./components/ConfirmationDialog.vue').default);
Vue.component('project-list', require('./components/ProjectList.vue').default);
Vue.component('student-list', require('./components/StudentList.vue').default);
Vue.component('admin-toggle', require('./components/AdminToggle.vue').default);
Vue.component('new-user', require('./components/NewUser.vue').default);
Vue.component('email-edit', require('./components/EmailEdit.vue').default);
Vue.component('manual-student-allocator', require('./components/ManualStudentAllocator.vue').default);
Vue.component('project-bulk-options', require('./components/ProjectBulkOptions.vue').default);
Vue.component('course-student-list', require('./components/CourseStudentList.vue').default);
Vue.component('research-area-admin', require('./components/ResearchAreaAdmin.vue').default);
Vue.component('deletable-list', require('./components/DeletableList.vue').default);
Vue.component('filterable-items', require('./components/FilterableItems.vue').default);

window.moment = require('moment');

Vue.directive('pikaday', {
  bind: (el, binding) => {
    el.pikadayInstance = new Pikaday({
      field: el,
      format: 'DD/MM/YYYY',
      onSelect: () => {
        const event = new Event('input', { bubbles: true });
        el.value = el.pikadayInstance.toString();
        el.dispatchEvent(event);
      },
      // add more Pikaday options below if you need
      // all available options are listed on https://github.com/dbushell/Pikaday
    });
  },

  unbind: (el) => {
    el.pikadayInstance.destroy();
  },
});

new Vue({
  el: '#app',

  data: {
    showConfirmation: false,
    openProjects: [],
    selectedStudent: null,
  },

  methods: {
    showUserUrl(userId) {
      return route('admin.user.show', userId);
    },

    getProjectUrl(projectId) {
      return route('project.show', projectId);
    },

    editProgrammeUrl(programmeId) {
      return route('admin.programme.edit', programmeId);
    },

    deleteProject(projectId) {
      this.showConfirmation = false;
      axios.delete(route('project.delete', projectId)).then((response) => {
        window.location = route('home');
      });
    },

    deleteCourse(courseId) {
      this.showConfirmation = false;
      axios
        .delete(route('admin.course.destroy', courseId))
        .then((response) => {
          window.location = route('admin.course.index');
        });
    },

    deleteProgramme(programmeId) {
      this.showConfirmation = false;
      axios
        .delete(route('admin.programme.destroy', programmeId))
        .then((response) => {
          window.location = route('admin.programme.index');
        });
    },

    deleteCourseStudents(courseId) {
      this.showConfirmation = false;
      axios
        .delete(route('course.remove_students', courseId))
        .then((response) => {
          window.location = route('admin.course.show', courseId);
        });
    },

    deleteStudents(category) {
      this.showConfirmation = false;
      axios
        .delete(route(`students.remove_${category}`))
        .then((response) => {
          window.location = route('admin.users', category);
        });
    },

    deleteUser(userId) {
      this.showConfirmation = false;
      axios.delete(route('admin.user.delete', userId)).then((response) => {
        window.location = route('home');
      });
    },
  },
});
