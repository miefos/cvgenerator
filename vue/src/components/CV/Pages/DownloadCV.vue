<template>
  <Message class="scalein animation-ease-in-out animation-duration-1000" v-if="!this.data?.left_minutes_for_payment" severity="error">{{ data.translations.youHaveNotPaid }}</Message>
  <a v-else target="_blank" :href="data.api.download_cv">
    <Button :label="data.translations.downloadCV" icon="pi pi-download" />
  </a>
</template>

<script>
export default {
  name: "DownloadCV",
  props: {
    data: {type: Object, required: true}
  },

}
</script>

<script setup>
import Button from "primevue/button"
import Message from "primevue/message"
</script>

<style scoped>

</style>