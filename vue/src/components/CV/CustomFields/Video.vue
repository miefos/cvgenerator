<template>
  <div v-if="videoPublicUrlKey !== 'not-set'">
    <VideoRecorder @recording-uploaded-successfully="setAction('videoplayer')" v-if="action === 'videorecorder'"
                   :data="{...data}"></VideoRecorder>
    <div v-if="action === 'videoplayer' && userHasVideo">
      <VideoPlayer
          @updateVideoSecond="(sec) => currentVideoSecond = sec"
          :key="waitingForResponse"
          :url="data.api.get_video + '?t=' + Date.now() + '&q=' + videoPublicUrlKey"
          :data="{...data}"
      ></VideoPlayer>
    </div>
    <div v-else-if="action === 'videoplayer' && !userHasVideo">
      <h4>{{ data.translations.noVideo }}</h4>
    </div>

    <!-- Buttons -->
    <div v-if="action !== 'videorecorder'" class="flex flex-wrap gap-2">
      <Button v-if="action === 'videoplayer' && userHasVideo" @click="deleteVideo"
              :label="data.translations.removeVideo" icon="pi pi-trash"/>
      <VideoUploader :data="{...data}"></VideoUploader>
      <Button @click="setAction('videorecorder')" :label="data.translations.recordVideo" icon="pi pi-video"/>
      <Button @click="setThumbnailFromSecond" v-if="userHasVideo" :label="data.translations.setThumbnailByVideoSeconds"
              icon="pi pi-video"/>
    </div>
    <div v-else>
      <Button type="button" @click="setAction('videoplayer')" :label="data.translations.viewVideo"
              icon="pi pi-arrow-left" class="my-2"/>
    </div>

    <!-- Thumbnail -->
    <div class="my-4" v-if="action === 'videoplayer' && userHasVideo">
      <div class="font-semibold">{{ data.translations.thumbnail }}</div>
      {{ data.translations.thumbnailDescription }}
      <div class="flex">
        <img
            width="300"
            :src="data.api.get_thumbnail + '?t=' + Date.now()"
            class="rounded-circle"
        />
      </div>
    </div>
    <div v-if="action === 'videoplayer' && userHasVideo">
      {{ data.translations.yourVideoPubliclyIsAvailableHere }}
      <a
          :href="data.api.get_video + '?t=' + Date.now() + '&q=' + videoPublicUrlKey"
          target="_blank">
        <Button type="button" outlined label="video"/>
      </a>
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
    ...mapState(['userHasVideo', "waitingForResponse", "videoPublicUrlKey"]),
  },
  data() {
    return {
      action: 'videoplayer',
      currentVideoSecond: null,
    };
  },
  async beforeCreate() {
    await this.$store.dispatch("setUserHasVideo", this.data.user_has_video);
    await this.$store.dispatch("setUserVideoKey", this.data.public_video_key);
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
      this.$store.dispatch('setThumbnailFromSeconds', {
        time: this.currentVideoSecond,
        apiUrl: this.data.api.set_thumbnail_by_video_second
      });
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