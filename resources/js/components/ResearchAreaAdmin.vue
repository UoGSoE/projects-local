<template>
    <div class="columns">
        <div class="column">
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h3 class="title is-3">
                            Research areas
                        </h3>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <new-research-area @add="add"></new-research-area>
                    </div>
                </div>
            </div>
            <transition-group name="fade">
                <research-area
                  v-for="area in orderedResearchAreas"
                  :key="area.id"
                  :area="area"
                  @destroy="remove">
                </research-area>
            </transition-group>
        </div>
        <div class="column">
        </div>
    </div>
</template>

<script>
let ResearchArea = require("./ResearchArea.vue").default;
let NewResearchArea = require("./NewResearchArea.vue").default;

export default {
  props: ["areas"],

  components: {
    "research-area": ResearchArea,
    "new-research-area": NewResearchArea
  },

  computed: {
    orderedResearchAreas() {
      return this.researchAreas.sort((a, b) => a.title > b.title);
    }
  },

  data() {
    return {
      researchAreas: this.areas
    };
  },

  methods: {
    add(data) {
      this.researchAreas.unshift(JSON.parse(data.area));
    },
    remove(id) {
      this.researchAreas = this.researchAreas.filter(area => area.id != id);
    }
  }
};
</script>
