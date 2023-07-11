<template>
    <template v-if="data.screen_type === 'cv_post_frontend_fields'">
      <CVApp :data="{...data}"></CVApp>
    </template>
    <Auth v-else-if="!data.logged_in" :data="{...data}"></Auth>
</template>

<script>

export default {
  components: {
  },
  props: {
    data: Object
  },
}
</script>

<script setup>
import Auth from "@/components/Auth/Auth.vue";
import CVApp from "@/components/CV/CVApp.vue";
</script>