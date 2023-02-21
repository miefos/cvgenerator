<template>
  <VideoRecorder @recording-uploaded-successfully="setAction('videoplayer')" v-if="action === 'videorecorder'" :data="{...data}"></VideoRecorder>
  <VideoPlayer v-if="action === 'videoplayer' && userHasVideo" :key="waitingForResponse" :url="data.api.get_video + '?t=' + Date.now()" :data="{...data}"></VideoPlayer>
  <div v-else-if="action === 'videoplayer' && !userHasVideo"><h4>{{ data.translations.noVideo }}</h4></div>
  <div v-if="action !== 'videorecorder'" class="flex">
    <Button v-if="action === 'videoplayer' && userHasVideo" @click="deleteVideo" :label="data.translations.removeVideo" icon="pi pi-trash" />
    <VideoUploader :class="action === 'videoplayer' && userHasVideo ? 'ml-2':''" :data="{...data}"></VideoUploader>
    <Button @click="setAction('videorecorder')" class="ml-2" :label="data.translations.recordVideo" icon="pi pi-video" />
  </div>
  <div v-else>
    <Button @click="setAction('videoplayer')" :label="data.translations.viewVideo" icon="pi pi-video" />
  </div>
</template>

<script>
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';
import VideoUploader from "@/components/CV/Components/Video/VideoUploader.vue";
import VideoPlayer from "@/components/CV/Components/Video/VideoPlayer.vue";
import VideoRecorder from "@/components/CV/Components/Video/VideoRecorder.vue";
import {mapState} from "vuex";

export default {
  name: 'Video',
  components: {VideoUploader, VideoRecorder, FileUpload, Button, VideoPlayer},
  props: {
    data: {type: Object, required: true}
  },
  computed: {
    ...mapState(['userHasVideo', "waitingForResponse"]),
  },
  data() {
    return {
      action: 'videoplayer'
    };
  },
  async created() {
    await this.$store.dispatch("setUserHasVideo", this.data.user_has_video);
  },
  methods: {
    deleteVideo() {
      if (confirm(this.data.translations.removeVideo + '?')) {
        this.$store.dispatch('removeVideo', this.data.api.remove_video);
      }
    },
    setAction(action) {
      this.action = action;
    }
  }
}
</script>

<style>
.p-fluid .p-button {
  width: auto !important;
}
</style>