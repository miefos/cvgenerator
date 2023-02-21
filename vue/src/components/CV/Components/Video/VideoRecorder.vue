<template>
  <div>
    <video ref="videoElement" width="640" height="480" autoplay muted></video>
    <div>
      <button v-if="!isRecording" @click.prevent="startRecording">Start Recording</button>
      <button v-if="isRecording" @click.prevent="stopRecording">Stop Recording</button>
    </div>
    <p v-if="isRecording">Recording: {{ elapsedTime }}</p>
  </div>
</template>

<script>
import RecordRTC from 'recordrtc';

export default {
  props: {
    data: {
      type: Object,
      required: true
    }
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
  methods: {
    async startRecording() {
      navigator.mediaDevices.getUserMedia({
        video: true,
        audio: true
      }).then(async (stream) => {
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
    async stopRecording() {
      if (this.recorder) {
        this.recorder.stopRecording(async () => {
          const webmBlob = this.recorder.getBlob();
          const theFile = new File([webmBlob], 'video.webm', { type: 'video/webm' });
          this.$store.dispatch('uploadVideo', {apiUrl: this.data.api.upload_video, theFile: theFile})
              .then(res => {
                if (res.data.status === 'ok') {
                  this.$emit('recording-uploaded-successfully');
                  this.mediaStream.getTracks().forEach((track) => track.stop());
                  this.$refs.videoElement.srcObject = null;
                  this.recorder = null;
                }
              })
          this.isRecording = false;
          clearInterval(this.intervalId);
          this.elapsedTime = 0;
        });
      }
    },
  },
};
</script>