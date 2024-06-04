<template>
  <div>

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>


    <div v-if="notif" class="si-hinweis text-green">{{ notif }}</div>


    <div class="si-form">
      <ul class="">
        <li>
          <label>Stundenplan Software</label>
          <div class="padding-l-l">{{ stundenplanSoftware }}</div>
        </li>
        <li>
          <div  class="padding-l-l">
            <div v-if="file" class="si-btn si-btn-off">{{ file.name }}</div>
          <label for="file-upload" class="si-btn si-btn-green"><i class="fa fa-file"></i>Datei auswählen</label>
          <input id="file-upload" type="file" @change="uploadFile" ref="file" class="hidden" />
        </div>
        </li>
        <li>
          <div>
          <label>Bestehende Tage überschreiben?</label>
          <button v-if="override == true" class="si-btn si-btn-toggle-on" v-on:click="handlerToggle"><i
              class="fa fas fa-toggle-on"></i> Ja
          </button>
          <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggle"><i class="fa fas fa-toggle-off"></i>
            Nein
          </button>
        </div>
        </li>
        <li>
          <button @click="submitFile" class="si-btn"><i class="fa fa-upload"></i>Upload</button>
        </li>
      </ul>
    </div>



  </div>
</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'

export default {
  components: {
    Error, Spinner
  },
  data() {
    return {
      selfURL: globals.selfURL,
      error: false,
      loading: false,
      notif: false,
      stundenplanSoftware: globals.stundenplanSoftware,

      file: false,
      override: true
    };
  },
  created: function () {

  },
  mounted() {

  },
  methods: {

    handlerToggle() {
      this.override = !this.override;
    },
    uploadFile() {
      this.file = this.$refs.file.files[0];
    },
    submitFile() {

      const formData = new FormData();
      formData.append('file', this.file);
      formData.append('override', this.override);
      this.loading = true;
      var that = this;
      const headers = { 'Content-Type': 'multipart/form-data' };
      axios.post(this.selfURL + '&task=upload', formData, { headers })
        .then((response) => {

          //response.data.files; // binary representation of the file
          //response.status; // HTTP status

          if (response.data) {
            if (response.data.error == false) {
              //that.loadList();
              console.log(response.data.msg);
              that.notif = response.data.msg;

            } else {
              that.error = '' + response.data.msg;
            }
          } else {
            that.error = 'Fehler beim Laden. 01';
          }

        })
        .catch(function () {
          that.error = 'Fehler beim Laden. 02';
        })
        .finally(function () {
          // always executed
          that.loading = false;
        });
    }


  }

};
</script>

<style></style>