<template>
  <div class="surface-card p-2 lg:p-5 shadow-2 border-round flex-auto scalein animation-duration-500">
    <form @submit.prevent="saveForm">
      <div class="text-900 font-semibold text-lg mt-3">
        <span v-html="section.icon" style="vertical-align: middle;"></span>
        <span style="margin-left: 8px;">{{ section.label }}</span>
      </div>

      <Divider></Divider>
      <Message class="scalein animation-ease-in-out animation-duration-1000" v-if="response?.data" :severity="response?.data?.status === 'ok' ? 'success' : 'error'">{{ response?.data?.msg }}</Message>

      <div class="flex gap-5 flex-column-reverse md:flex-row">
        <div class="flex-auto p-fluid max-w-full">
          <div class="mb-4" v-for="field in section.fields" :key="field.id">
            <MyField :field="{...field}" :data="{...data}" :section="{...section}" />
          </div>
          <div class="flex gap-2" v-if="section.save_button">
<!--            <MyButton :submit-button="true" :label="data.translations.submit" btn-internal-id="my-save-button-1" />-->
            <MyButton v-if="notLastSection()" @pressed="(e) => saveForm(e, true)" :label="data.translations.submit_and_continue" btn-internal-id="my-save-button-2" />
            <MyButton v-if="!notLastSection()" @pressed="(e) => saveForm(e, false, true)" :label="data.translations.finish_cv" btn-internal-id="my-save-button-3" />
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import {mapState} from "vuex"

export default {
  name: "CVEditSection",
  props: {
    section: {type: Object},
    data: {type: Object}
  },
  computed: {
    ...mapState(['response', 'waitingForResponse', 'responseReceivedRecently']),
    successfulResponse() {
      return this.$store.dispatch('successfulResponse')
    }
  },
  data() {
    return {}
  },
  methods: {
    notLastSection() {
      return this.section.id !== this.data.sections[this.data.sections.length-1].id
    },
    async saveForm(e, continueToNextSection = false, last = false) {
      const fieldValues = await this.$store.dispatch('getSectionFieldValues', {sectionId: this.section.id})

      this.$store.dispatch('validateForm', {fieldsValues: fieldValues, fieldParameters: this.section.fields})
          .then(resp => {
            this.$store.dispatch('saveForm', {
              apiUrl: this.data.api.update_cv,
              formName: this.section.id,
              fieldParameters: this.section.fields,
            }).then(resp => {
              if (resp.data.status === 'ok') {
                if (continueToNextSection) {
                  this.$emit('move-to-next-section')
                } else if (last) {
                  this.$router.push('/')
                }
              }
            }).catch(err => {
              console.log("Error 2003000")
            })
          })
          .catch(err => {
            console.log("Error")
          })
    }
  }
}
</script>

<script setup>
import ProgressSpinner from "primevue/progressspinner";
import Button from "primevue/button";
import Message from "primevue/message";
import Divider from "primevue/divider";
import MyField from "@/components/CV/Components/MyField.vue";
import MyButton from "@/components/CV/Components/MyButton.vue";
</script>