<template>
  <FileUpload :chooseLabel="data.translations.uploadVideo" mode="basic" accept="video/*" :customUpload="true" @uploader="uploadVideo" :auto="true"/>
</template>

<script>
import FileUpload from 'primevue/fileupload';

export default {
  name: "VideoUploader",
  components: {FileUpload},
  props: {
    data: {type: Object, required: true}
  },
  data() {
    return {
    }
  },
  methods: {
    uploadVideo(e) {
      const theFile = e.files[0]
      this.$store.dispatch('uploadVideo', {apiUrl: this.data.api.upload_video, theFile: theFile})
    }
  }
}
</script>
