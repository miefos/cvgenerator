<template>
  <a target="_blank" :href="data.api.download_cv">Download my CV</a>
</template>

<script>
export default {
  name: "DownloadCV",
  props: {
    data: {type: Object, required: true}
  },

}
</script>

<style scoped>

</style>