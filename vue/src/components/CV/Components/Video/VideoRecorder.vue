<template>
  <video ref="videoElement" autoplay muted style="max-width:100%;"></video>
  <div id="videoRecorderSelectingDeviceDropdowns">
    <select v-model="selectedVideoDevice" @change="startVideo" style="max-width:100%;">
      <option v-for="device in videoDevices" :key="device.deviceId" :value="device.deviceId">
        {{ device.label }}
      </option>
    </select>
    <select v-model="selectedAudioDevice" @change="startVideo" style="max-width:100%;">
      <option v-for="device in audioDevices" :key="device.deviceId" :value="device.deviceId">
        {{ device.label }}
      </option>
    </select>
  </div>
  <div>
    <Button v-if="!isRecording" @click.prevent="startRecording" :label="data.translations.startRecording" icon="pi pi-video"/>
    <Button v-else @click.prevent="() => stopRecording(true)" :label="data.translations.stopRecording" icon="pi pi-video"/>
  </div>
  <p v-if="isRecording">Recording: {{ elapsedTime }} / 120</p>
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
      videoDevices: [],
      audioDevices: [],
      selectedVideoDevice: null,
      selectedAudioDevice: null,
    };
  },
  async mounted() {
    const constraints = { audio: true, video: true };
    await navigator.mediaDevices.getUserMedia(constraints);

    // this.mediaStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    // this.$refs.videoElement.srcObject = this.mediaStream;
    const devices = await navigator.mediaDevices.enumerateDevices();
    this.videoDevices = devices.filter(device => device.kind === 'videoinput');
    this.audioDevices = devices.filter(device => device.kind === 'audioinput');

    // Set the default devices
    this.selectedVideoDevice = this.videoDevices.length ? this.videoDevices[0].deviceId : null;
    this.selectedAudioDevice = this.audioDevices.length ? this.audioDevices[0].deviceId : null;

    // Start the video with the default devices
    await this.startVideo();
  },
  emits: ["recording-uploaded-successfully"],
  beforeUnmount() {
    this.stopRecording()
  },
  methods: {
      async startVideo() {
        if (!this.selectedVideoDevice || !this.selectedAudioDevice) {
          return;
        }

        const constraints = {
          video: { deviceId: this.selectedVideoDevice },
          audio: { deviceId: this.selectedAudioDevice },
        };

        console.log(await navigator.mediaDevices.enumerateDevices())
        console.log(constraints)

        this.mediaStream = await navigator.mediaDevices.getUserMedia(constraints)
        this.$refs.videoElement.srcObject = this.mediaStream;
    },
    async startRecording() {
      const constraints = {
        video: { deviceId: this.selectedVideoDevice },
        audio: { deviceId: this.selectedAudioDevice },
      };

      navigator.mediaDevices.getUserMedia(constraints).then(async (stream) => {
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

        // Automatically stop recording after 120 seconds
        setTimeout(() => {
          this.stopRecording(true);
        }, 120 * 1000); // 120 seconds
      });
    },
    async stopRecording(shouldUpload = false) {
      this.isRecording = false;
      clearInterval(this.intervalId);
      this.elapsedTime = 0;

      if (this.recorder) {
        // this.recorder.setRecordingDuration(this.elapsedTime)
        this.recorder.stopRecording(async () => {
          console.log(this.recorder)
          const webmBlob = this.recorder.getBlob();
          this.stopTracksAndReset()
          const theFile = new File([webmBlob], 'video.webm', {type: 'video/webm'});
          if (shouldUpload) {
            this.$store.dispatch('uploadVideo', {apiUrl: this.data.api.upload_video, theFile: theFile})
                .then(res => {
                  if (res.data.status === 'ok') {
                    this.$emit('recording-uploaded-successfully');
                  }
                })
          }
        });
      }
    },
    stopTracksAndReset() {
      if (this.recorder.camera) {
        this.recorder.camera.stop()
      }
      if (this.mediaStream) {
        this.mediaStream.getTracks().forEach((track) => {
          track.stop();
          this.mediaStream.removeTrack(track);
        });
      }
      if (this.$refs.videoElement) {
        this.$refs.videoElement.srcObject = null;
      }
      this.recorder = null;
    }
  },
};
</script>