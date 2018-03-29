<template>
    <div>
        <div class="field">
            <div class="control">
                <div class="select">
                  <select v-model="selectedProgramme" name="programmes">
                    <option v-bind:value="-1">For any degree programme</option>
                    <option v-for="programme in programmes" v-bind:value="programme.title">{{ programme.title }}</option>
                  </select>
                </div>
            </div>
        </div>

        <div class="box" v-for="project in availableProjects" :key="project.id">
            <h4 class="title is-4">
                <button class="button" :class="{ 'is-info': isChosen(project.id) }" @click.prevent="expandProject(project.id)" :id='"expand-" + project.id' title="Show full description">
                    <span v-if="isExpanded(project.id)" class="icon">
                        -
                    </span>
                    <span v-else class="icon">
                        +
                    </span>
                </button>
                {{ project.title }}
            </h4>
            <p class="subtitle has-text-grey-light">
                Run by {{ project.owner.full_name }}
            </p>
            <div v-if="isExpanded(project.id)">
                <h5 class="title is-5 has-text-grey">Description</h5>
                <p>
                    {{ project.description }}
                </p>
                <span v-if="project.pre_req">
                    <br />
                    <h5 class="title is-5 has-text-grey">Prerequisite Skills</h5>
                    <p>
                        {{ project.pre_req }}
                    </p>
                </span>
                <hr />
                <div class="level">
                    <div class="level-left has-text-weight-semibold has-text-grey">
                        <div class="level-item">
                            Make this project my
                        </div>
                        <div class="level-item">
                            <div class="buttons has-addons">
                                <span class="button" :class="{ 'is-info': (choices.first == project.id) }" @click="choose('first', project.id)" :id='"project-" + project.id + "-first"'>
                                    1st
                                </span>
                                <span class="button" :class="{ 'is-info': (choices.second == project.id) }" @click="choose('second', project.id)" :id='"project-" + project.id + "-second"'>
                                    2nd
                                </span>
                                <span class="button" :class="{ 'is-info': (choices.third == project.id) }" @click="choose('third', project.id)" :id='"project-" + project.id + "-third"'>
                                    3rd
                                </span>
                                <span class="button" :class="{ 'is-info': (choices.fourth == project.id) }" @click="choose('fourth', project.id)" :id='"project-" + project.id + "-fourth"'>
                                    4th
                                </span>
                                <span class="button" :class="{ 'is-info': (choices.fifth == project.id) }" @click="choose('fifth', project.id)" :id='"project-" + project.id + "-fifth"'>
                                    5th
                                </span>
                            </div>
                        </div>
                        <div class="level-item">
                            preference
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <transition name="fade">
        <div v-if="anyProjectsChosen" id="infobox">
            <article class="message" :class="{'is-success': numberChosen == requiredChoices}">
              <div class="message-body" v-if="numberChosen < requiredChoices" :key="numberChosen < requiredChoices">
                You have chosen {{ numberChosen }} {{ numberChosen > 1 ? 'projects' : 'project' }}. You need to choose {{ requiredChoices - numberChosen }} more.
              </div>
              <div class="message-body" v-else :key="numberChosen < requiredChoices">
                You have chosen all {{ requiredChoices }} projects - you can now submit your choices.<br />
                <p>&nbsp;</p>
                <button class="button is-info" :class="{'is-danger': submissionError}" :disabled="submissionError" @click.prevent="submitChoices">{{ submitButtonText }}</button>
              </div>
            </article>
        </div>
        </transition>

        <p>&nbsp;</p>
    </div>

</template>

<script>
    export default {
        props: ['projects', 'programmes'],

        data() {
            return {
                showConfirmation: false,
                openProjects: [],
                selectedStudent: null,
                requiredChoices: window.config.required_choices,
                submitButtonText: 'Submit my choices',
                submissionError: false,
                selectedProgramme: -1,
                choices: {
                    first: null,
                    second: null,
                    third: null,
                    fourth: null,
                    fifth: null
                }
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
            },
            availableProjects() {
                if (this.selectedProgramme == -1) {
                    return this.projects;
                }
                return this.projects.filter(project => {
                    return project.programmes.find(programme => {
                        if (programme.title == this.selectedProgramme) {
                            return true;
                        }
                    })
                });
            }
        },

        methods: {
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
                        this.submitButtonText = 'Error submitting choices - sorry';
                        this.submissionError = true;
                        console.log(error);
                     });
            }
        }
    }
</script>
