<template>
  <video ref="videoElement" width="640" height="480" autoplay muted></video>
  <div>
    <Button v-if="!isRecording" @click.prevent="startRecording" :label="data.translations.startRecording" icon="pi pi-video"/>
    <Button v-else @click.prevent="() => stopRecording(true)" :label="data.translations.stopRecording" icon="pi pi-video"/>
  </div>
  <p v-if="isRecording">Recording: {{ elapsedTime }}</p>
</template>

<script>
import RecordRTC from 'recordrtc';
import Button from "primevue/button";

export default {
  props: {
    data: {
      type: Object,
      required: true
    }
  },
  components: {
    Button
  },
  data() {
    return {
      mediaStream: null,
      recorder: null,
      isRecording: false,
      elapsedTime: 0,
      intervalId: null,
    };
  },
  async mounted() {
    this.mediaStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    this.$refs.videoElement.srcObject = this.mediaStream;
  },
  emits: ["recording-uploaded-successfully"],
  beforeUnmount() {
    this.stopRecording()
  },
  methods: {
    async startRecording() {
      navigator.mediaDevices.getUserMedia({video: true, audio: true}).then(async (stream) => {
        this.recorder = RecordRTC(stream, {
          type: 'video',
          mimeType: 'video/webm',
          videoBitsPerSecond: 5000000, // 5 Mbps
        });
        this.recorder.startRecording();
        this.isRecording = true;
        this.intervalId = setInterval(() => {
          this.elapsedTime += 1;
        }, 1000);
      });
    },
    async stopRecording(shouldUpload = false) {
      if (this.recorder) {
        this.recorder.stopRecording(async () => {
          const webmBlob = this.recorder.getBlob();
          const theFile = new File([webmBlob], 'video.webm', {type: 'video/webm'});
          if (shouldUpload) {
            this.$store.dispatch('uploadVideo', {apiUrl: this.data.api.upload_video, theFile: theFile})
              .then(res => {
                if (res.data.status === 'ok') {
                  this.stopTracksAndReset()
                  this.$emit('recording-uploaded-successfully');
                }
              })
          } else {
            this.stopTracksAndReset()
          }
        });
      } else {
        this.stopTracksAndReset()
      }
    },
    stopTracksAndReset() {
      console.log('stopped')
      this.isRecording = false;
      clearInterval(this.intervalId);
      this.elapsedTime = 0;

      if (this.mediaStream) {
        console.log('is mediaStream')
        console.log('mediaStream', this.mediaStream)
        this.mediaStream.getTracks().forEach((track) => {
          console.log('track', track)
          track.stop();
          this.mediaStream.removeTrack(track);
        });
      }
      if (this.$refs.videoElement) {
        console.log('is videoElement')
        this.$refs.videoElement.srcObject = null;
      }
      console.log('mediaStream', this.mediaStream)
      this.recorder = null;
    }
  },
};
</script>