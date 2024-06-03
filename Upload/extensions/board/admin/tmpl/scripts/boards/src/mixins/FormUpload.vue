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
    <uploader-unsupport></uploader-unsupport>
    <uploader-btn class="si-btn">Datei hochladen</uploader-btn>
    <!--
    <uploader-drop>
      <p>Drop files here to upload or .....</p>
      <uploader-btn class="si-btn">Upload</uploader-btn>

      <uploader-btn :attrs="attrs">select images</uploader-btn>
      <uploader-btn :directory="true">select folder2</uploader-btn>

    </uploader-drop>
    -->
    <uploader-list></uploader-list>
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
      singleFile: true
    }
    const attrs = {
      accept: 'image/*'
    }
    const statusText = {
      success: 'Ok',
      error: 'Fehler',
      uploading: 'loading...',
      paused: 'paused',
      waiting: 'waiting'
    }
    const complete = () => {
      //console.log('complete', arguments)
      emit('done', arguments);

      //console.log(window.uploader)

    }
    const fileComplete = () => {
      //console.log('file complete', arguments)
    }

    const fileSuccess = (rootFile, file, message)  => {
      //console.log('file success', rootFile.file.name, JSON.parse(message))
      emit('fileSuccess', rootFile.file.name, JSON.parse(message));
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

.uploader-btn {
  box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 15px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px  !important;
  border-radius: 3rem !important;
  display: block !important;
  display: inline-block !important;
  padding: 1rem 1.6rem !important;
  margin-bottom: 0.3rem !important;
  margin-top: 0.3rem !important;
  font-size: 11pt !important;
  font-weight: 300 !important;
  line-height: 100% !important;
  letter-spacing: 0.75pt !important;
  text-align: center !important;
  vertical-align: middle !important;
  -ms-touch-action: manipulation !important;
  touch-action: manipulation !important;
  cursor: pointer !important;
  -webkit-user-select: none !important;
  -moz-user-select: none !important;
  -ms-user-select: none !important;
  user-select: none !important;
  background-image: none !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  white-space: nowrap !important;
  background-color: #3c8dbc !important;
  border: 1px solid #3c8dbc !important;
  color: #fff !important;

}


.uploader-list {
  margin-top: 1rem;
}
.uploader-list li {
  padding: 0 !important;
}
.uploader-list li:nth-child(odd) {
  background-color: #fff !important;
}

.uploader-list .uploader-file-icon {
  display: none;
}

.uploader-file {
  border: none !important;
}

.uploader-file-progress {
  background: none !important;
}

</style>