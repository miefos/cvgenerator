<template>
  <div id="cvPage">
    <!-- Navigation -->
    <div class="flex justify-content-center">
      <Navigation :tabs="tabs"></Navigation>
    </div>

    <!-- Page -->
    <router-view :data="{...data}"></router-view>

  </div>
</template>

<script>
import Navigation from "@/components/CV/Components/Navigation.vue";
import Home from "@/components/CV/Pages/Home.vue";
import EditCV from "@/components/CV/Pages/EditCV.vue";
import DownloadCV from "@/components/CV/Pages/DownloadCV.vue";
export default {
  name: "CV",
  components: {DownloadCV, EditCV, Home, Navigation},
  props: {data: {type: Object, required: true}},
  data() {
    return {
      tabs: this.data.navigation_tabs
    }
  },
  methods: {
    getSeverity(status) {
      switch (status) {
        case "ok":
          return "success";
        case "fail":
          return "error";
        default:
          return "info";
      }
    }
  }
}
</script>

<script setup>
import Message from "primevue/message";
</script>