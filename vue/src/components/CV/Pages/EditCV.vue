<template>
  <BlockUI :blocked="waitingForResponse">
    <ProgressBar v-if="waitingForResponse" mode="indeterminate" />
    <div class="surface-ground px-4 py-8 md:px-6 lg:px-8">
      <div class="p-fluid flex flex-column lg:flex-row">
        <ul style="min-width: 30%;" class="list-none m-0 p-0 flex flex-row lg:flex-column justify-content-evenly md:justify-content-between lg:justify-content-start mb-5 lg:pr-8 lg:mb-0 flex-wrap">
          <li v-for="(section, i) in data.sections" class="min-w-full">
            <a @click="activeTab = i" v-ripple class="min-w-full flex align-items-center cursor-pointer p-3 border-round text-800 hover:surface-hover transition-duration-150 transition-colors p-ripple justify-content-center lg:justify-content-start">
              <i class="pi mr-2" :class="section.icon"></i>
              <span class="font-medium">{{ section.label }}</span>
            </a>
          </li>
        </ul>
        <template v-for="(section, i) in data.sections" >
          <CVEditSection @moveToNextSection="moveToNextSection" v-if="activeTab === i" :section="section" :data="{...data}" :key="i" />
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
  props: {data: Object},
  data() {
    return {
      activeTab: 4
    }
  },
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
      this.activeTab = Math.min(this.activeTab + 1, this.data.sections.length - 1)
    }
  }
}
</script>

<script setup>
import BlockUI from "primevue/blockui"
import ProgressBar from "primevue/progressbar"
</script>