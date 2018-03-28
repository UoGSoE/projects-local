<div v-if="anyProjectsChosen">
    <article class="message" :class="{'is-success': numberChosen == requiredChoices}">
      <div class="message-body" v-if="numberChosen < requiredChoices">
        You have chosen @{{ numberChosen }} projects. You need to choose @{{ requiredChoices - numberChosen }} more.
      </div>
      <div class="message-body" v-else>
        You have chosen @{{ requiredChoices }} projects - you can now submit your choices.<br />
        <p>&nbsp;</p>
        <button class="button is-info" @click.prevent="submitChoices">Submit my choices</button>
      </div>
    </article>
</div>
