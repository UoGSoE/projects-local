<template>
    <div class="field has-addons">
        <p class="control">
            <span class="button is-static">Programme:</span>
        </p>
        <div class="select">
            <select
                style="border-radius: 0px 4px 4px 0px"
                @change="onChange($event)">
                <option value='' :selected="programmeFilter === ''">All</option>
                <option
                  v-for="programme in programmes"
                  :key="programme.id"
                  :value="programme.title"
                  :selected="programmeFilter === programme.title"
                >{{ programme.title }}</option>
            </select>
        </div>
    </div>
</template>

<script>
export default {
    props: ['category', 'type', 'programmes', 'programme'],

    data() {
        return {
            categoryFilter: this.category,
            typeFilter: this.type,
            programmeFilter: this.programme
        }
    },

    methods: {
        onChange: function(event) {
            window.location.href = route('admin.project.index', {
                'category': this.categoryFilter,
                'type': this.typeFilter,
                'programme': event.target.value,
            })
        }
    }
}
</script>
