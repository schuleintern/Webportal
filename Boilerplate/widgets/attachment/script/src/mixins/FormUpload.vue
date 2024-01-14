<template>
  <uploader
      :options="options"
      :file-status-text="statusText"
      class="uploader-example"
      ref="uploaderRef"
      @file-complete="fileComplete"
      @file-success="fileSuccess"
      @complete="complete"
  >
    <!-- <uploader-unsupport></uploader-unsupport>
    <uploader-drop>
      <p>Drop files here to upload or</p>
      <uploader-btn>select files</uploader-btn>
      <uploader-btn :attrs="attrs">select images</uploader-btn>
      <uploader-btn :directory="true">select folder</uploader-btn>
    </uploader-drop>
    <uploader-list></uploader-list> -->
  </uploader>
</template>

<script>
import { nextTick, ref, onMounted } from 'vue'


export default {
  props: {
    target: String
  },
  setup (props, { emit }) {
    const uploaderRef = ref(null)
    const options = {
      target: props.target,
      testChunks: false,
      singleFile: false
    }
    const attrs = {
      accept: 'image/*'
    }
    const statusText = {
      success: 'success',
      error: 'error',
      uploading: 'uploading',
      paused: 'paused',
      waiting: 'waiting'
    }
    const complete = () => {
      //console.log('complete', arguments)
      emit('done', arguments);
    }
    const fileComplete = () => {
      //console.log('file complete', arguments)
    }

    const fileSuccess = ()  => {

    }


    onMounted(() => {
      nextTick(() => {
        window.uploader = uploaderRef.value.uploader
      })
    })
    return {
      uploaderRef,
      options,
      attrs,
      statusText,
      complete,
      fileComplete,
      fileSuccess
    }
  }
}
</script>

<style>
.uploader-example {
  width: 880px;
  padding: 15px;
  margin: 40px auto 0;
  font-size: 12px;
  box-shadow: 0 0 10px rgba(0, 0, 0, .4);
}
.uploader-example .uploader-btn {
  margin-right: 4px;
}
.uploader-example .uploader-list {
  max-height: 440px;
  overflow: auto;
  overflow-x: hidden;
  overflow-y: auto;
}
</style>