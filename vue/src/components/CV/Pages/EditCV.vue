<template>
  <BlockUI :blocked="waitingForResponse">
    <ProgressBar v-if="waitingForResponse" mode="indeterminate" />
    <div class="surface-ground px-1 py-2 lg:px-4 lg:py-8 lg:px-8">
      <div class="flex flex-wrap" style="max-width: 100%;">
        <ul style="min-width: 300px;" class="list-none m-0 p-0 flex lg:flex-column mb-5 lg:pr-8 lg:mb-0 flex-wrap">
          <li v-for="(section, i) in data.sections" class="min-w-full">
            <router-link :to="{query: {section: section.id}}" class="min-w-full flex  cursor-pointer p-3 border-round text-800 hover:surface-hover transition-duration-150 transition-colors vertical-align-middle">
              <div>
                <span v-html="section.icon" style="vertical-align: middle; margin-top: 1px;"></span>
                <span class="font-medium" style="margin-left: 8px;">{{ section.label }}</span>
              </div>
            </router-link>
          </li>
        </ul>
        <template v-for="(section, i) in data.sections">
          <div class="flex-1 max-w-full" v-if="section.id === sectionId">
            <CVEditSection @moveToNextSection="moveToNextSection" :section="section" :data="{...data}" :key="i" />
          </div>
        </template>
      </div>
    </div>
  </BlockUI>
</template>

<script>
import CVEditSection from "@/components/CV/Components/CVEditSection.vue";
import {mapState} from "vuex";
export default {
  name: "EditCV",
  components: {CVEditSection},
  computed: {
    ...mapState(['waitingForResponse'])
  },
  props: {data: Object, sectionId: String},
  created() {
    this.$store.dispatch('setNonce', {nonceName: this.data.nonce_name, nonceValue: this.data.nonce})

    // set initial field values
    this.data.sections.forEach(section => {
      this.$store.dispatch('addFormSection', section.id)
      section.fields.forEach(field => {
        let metaValue = this.data.meta[field.id]?.[0] ?? ""
        this.$store.dispatch('addInitialFieldData', {sectionId: section.id, field: field, fieldValue: metaValue}) // this checks if the field is json
      })
    })
  },
  methods: {
    moveToNextSection() {
      document.getElementById('cv_generator').scrollIntoView({behavior: "smooth"});
      const currentSectionIndex = this.data.sections.findIndex(section => section.id === this.sectionId)
      if (currentSectionIndex > -1 && this.data.sections[currentSectionIndex + 1] !== undefined) {
        this.$router.push({query: {section: this.data.sections[currentSectionIndex + 1].id }})
      }
    }
  }
}
</script>

<script setup>
import BlockUI from "primevue/blockui"
import ProgressBar from "primevue/progressbar"
</script>