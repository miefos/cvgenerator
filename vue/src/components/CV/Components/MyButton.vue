<template>
  <Button @click="setLastButtonPressed" :type="submitButton ? 'submit' : 'button'"  class="w-auto">
    {{ label }}
    <div class="">
      <ProgressSpinner v-if="waitingForResponse && lastButtonPressedId === btnInternalId" class="scalein ml-2 animation-duration-500" style="width: 20px;height:auto;" strokeWidth="5" />
      <i class="pi ml-2 pi-check scalein animation-duration-500" v-else-if="responseReceivedRecently && successfulResponse && lastButtonPressedId === btnInternalId"></i>
      <i class="pi ml-2 pi-times scalein animation-duration-500" v-else-if="responseReceivedRecently && (!successfulResponse || Object.keys(errors).length > 0) && lastButtonPressedId === btnInternalId"></i>
      <i class="pi ml-2 pi-save scalein animation-duration-500" v-else></i>
    </div>
  </Button>
</template>

<script>
import {mapState} from "vuex";

export default {
  name: "MyButton",
  props: {
    submitButton: {type: Boolean, default: false},
    data: {type: Object,},
    label: {type: String,},
    btnInternalId: {type: String,},
  },
  computed: {
    ...mapState(['response', 'waitingForResponse', 'responseReceivedRecently', 'lastButtonPressedId', 'errors']),
    successfulResponse() {
      return this.$store.dispatch('successfulResponse')
    }
  },
  methods: {
    setLastButtonPressed() {
      this.$emit('pressed')
      this.$store.dispatch('setLastButtonPressed', this.btnInternalId)
    },
  },
}
</script>

<script setup>
import ProgressSpinner from "primevue/progressspinner";
import Button from "primevue/button";
</script>