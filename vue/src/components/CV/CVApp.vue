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
export default {
  name: "CV",
  components: {Navigation},
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