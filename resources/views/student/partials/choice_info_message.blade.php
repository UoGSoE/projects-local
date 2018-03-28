<transition name="fade">
<div v-if="anyProjectsChosen" id="infobox">
    <article class="message" :class="{'is-success': numberChosen == requiredChoices}">
      <div class="message-body" v-if="numberChosen < requiredChoices" :key="numberChosen < requiredChoices">
        You have chosen @{{ numberChosen }} @{{ numberChosen > 1 ? 'projects' : 'project' }}. You need to choose @{{ requiredChoices - numberChosen }} more.
      </div>
      <div class="message-body" v-else :key="numberChosen < requiredChoices">
        You have chosen @{{ requiredChoices }} projects - you can now submit your choices.<br />
        <p>&nbsp;</p>
        <button class="button is-info" :class="{'is-danger': submissionError}" :disabled="submissionError" @click.prevent="submitChoices">@{{ submitButtonText }}</button>
      </div>
    </article>
</div>
</transition>