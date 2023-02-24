<template>
  <div ref="videoPlayerDiv" class="my-2">
      <video class="video-js vjs-theme-city vjs-big-play-centered">
        <source :src="url" type="video/mp4" />
        <source :src="url" type="video/webm" />
        <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that<a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
      </video>
  </div>
</template>

<script>
import 'video.js/dist/video-js.css'
import videojs from 'video.js'

export default {
  name: "VideoThumbnailSelector",
  props: {url: {type: String, required: true,}, data: {type: Object, required: true,}},
  emits: ['updateVideoSecond'],
  data() {
    return {
      player: null,
      options: {
        controls: true,
        width: 640,
        liveui: false, // disable live message,
        preload: 'auto',
      },
    }
  },
  mounted() {
    const videoTag = this.$refs.videoPlayerDiv.querySelector('video');
    this.player = videojs(videoTag, this.options)

    this.player.on('timeupdate', () => {
      this.$emit('updateVideoSecond', this.player.currentTime());
    });
  },
  beforeDestroy() {
    if (this.player) {
      this.player.dispose();
    }
  },
  methods: {
  }
};
</script>