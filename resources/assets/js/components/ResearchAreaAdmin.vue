<template>
    <div>
        <transition-group name="fade">
            <research-area v-for="area in researchAreas" :key="area.id" :area="area" @destroy="remove"></research-area>
        </transition-group>
        <hr />
        <new-research-area @add="add"></new-research-area>
    </div>
</template>

<script>
let ResearchArea = require("./ResearchArea.vue");
let NewResearchArea = require("./NewResearchArea.vue");

export default {
  props: ["areas"],

  components: {
    "research-area": ResearchArea,
    "new-research-area": NewResearchArea
  },

  data() {
    return {
      researchAreas: this.areas
    };
  },

  methods: {
    add(data) {
      this.researchAreas.push(JSON.parse(data.area));
    },
    remove(id) {
      this.researchAreas = this.researchAreas.filter(area => area.id != id);
    }
  }
};
</script>
