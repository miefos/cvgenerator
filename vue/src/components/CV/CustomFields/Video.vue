<template>
  <VideoRecorder @recording-uploaded-successfully="setAction('videoplayer')" v-if="action === 'videorecorder'" :data="{...data}"></VideoRecorder>
  <VideoPlayer @updateVideoSecond="(sec) => currentVideoSecond = sec" v-if="action === 'videoplayer' && userHasVideo" :key="waitingForResponse" :url="data.api.get_video + '?t=' + Date.now()" :data="{...data}"></VideoPlayer>
  <div v-else-if="action === 'videoplayer' && !userHasVideo">
    <h4>{{ data.translations.noVideo }}</h4>
  </div>

  <!-- Buttons -->
  <div v-if="action !== 'videorecorder'" class="flex flex-wrap gap-2">
    <Button v-if="action === 'videoplayer' && userHasVideo" @click="deleteVideo" :label="data.translations.removeVideo" icon="pi pi-trash" />
    <VideoUploader :data="{...data}"></VideoUploader>
    <Button @click="setAction('videorecorder')" :label="data.translations.recordVideo" icon="pi pi-video" />
    <Button @click="setThumbnailFromSecond" class="" :label="data.translations.setThumbnailByVideoSeconds" icon="pi pi-video" />
  </div>
  <div v-else>
    <Button type="button" @click="setAction('videoplayer')" :label="data.translations.viewVideo" icon="pi pi-arrow-left" class="my-2" />
  </div>

  <!-- Thumbnail -->
  <div class="my-4" v-if="action === 'videoplayer'" >
    {{ data.translations.thumbnail }}
    <div class="flex">
        <img width="300" :src="data.api.get_thumbnail + '?t=' + new Date()" class="rounded-circle" />
    </div>
  </div>
</template>

<script>
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';
import VideoUploader from "@/components/CV/Components/Video/VideoUploader.vue";
import VideoPlayer from "@/components/CV/Components/Video/VideoPlayer.vue";
import VideoRecorder from "@/components/CV/Components/Video/VideoRecorder.vue";
import {mapState} from "vuex";
import InputNumber from 'primevue/inputnumber';

export default {
  name: 'Video',
  components: {VideoUploader, VideoRecorder, FileUpload, Button, VideoPlayer, InputNumber},
  props: {
    data: {type: Object, required: true}
  },
  computed: {
    ...mapState(['userHasVideo', "waitingForResponse"]),
  },
  data() {
    return {
      action: 'videoplayer',
      currentVideoSecond: null,
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
    },
    setThumbnailFromSecond() {
      this.$store.dispatch('setThumbnailFromSeconds', {time: this.currentVideoSecond, apiUrl: this.data.api.set_thumbnail_by_video_second});
    }
  }
}
</script>

<style>
.p-fluid .p-button,
.p-fluid .p-inputnumber {
  width: auto !important;
}

</style>