<template>

  <div class=" padding-l">
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <div v-if="!item && !uploadDone">
      <h3><i class="fa fa-sync-alt"></i> Aktualisierung der Schüler / Eltern und Lehrerdaten aus der ASV</h3>

      <br>
      <div class="text-bold">Benötigter Export: "Export für eine Notenverwaltung"</div>
      <br>
      Der Import aktualisiert folgende Daten:<br>
      - Schülerdaten<br>
      - Elterndaten / Kontaktdaten<br>
      - Lehrerdaten (Kürzel, Amtsbezeichnungen, Unterricht)<br>
      - Unterrichtsübersicht<br>
      <br>
      Der Export kann jederzeit vorgenommen werden.
      <br><br>
      Letzter ASV Import: {{lastASV}}
      <br><br>
      <FormUpload @done="handerUpload" :target="apiURL+'/setAsv/'+randFile"></FormUpload>
    </div>

    <div v-if="!item && uploadDone" class=" width-55vw">
      <div class="si-form">
        <ul>
          <li>
            <div class="si-hinweis">
              <h3>Die ASV-ZIP-Datei wurde erfolgreich hochgeladen.</h3>
              Für den vollständigen Import müssen sie noch das Passwort eingeben und bestätigen.
            </div>
          </li>
          <li>
            <label>ZIP Passwort</label>
            <input name="extImport_asv_password" type="text" v-model="password" class="width-20rem">
          </li>
          <li>
            <button @click="handlerUnzip" class="si-btn"><i class="fa fa-cogs"></i>Importieren</button>
          </li>
        </ul>
      </div>
    </div>

    <div v-if="item && uploadDone" class=" width-55vw">
      <div class="si-details">
        <ul>
          <li>
            <div class="si-hinweis">
              <h3 class="text-green">Der ASV-Import war erfolgreich.</h3>
              Im Folgenden finden sie das Protokoll:
            </div>
          </li>
          <li v-bind:key="index" v-for="(obj, index) in  item" class="">
            {{ obj.msg }}
          </li>
        </ul>
      </div>

    </div>


  </div>
</template>

<script>
import FormUpload from './mixins/FormUpload.vue'
import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

const axios = require('axios').default;

export default {
  name: 'App',
  components: {
    AjaxError, AjaxSpinner, FormUpload
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      randFile: window.globals.randFile,
      lastASV: window.globals.lastASV,
      acl: window.globals.acl,
      error: false,
      loading: false,
      page: 'list',


      uploadDone: false,
      item: false,
      password: ''

    };
  },
  computed: {},

  created: function () {

    // INIT
    /*
    this.$bus.$on('page--open', data => {
      if (data.props) {
        data.item.props = data.props;
      }
      this.handlerPage(data.page, data.item);
    });
    */


  },
  methods: {

    handerUpload() {

      this.uploadDone = true;
    },

    handlerUnzip() {

      if (!this.password || !this.randFile) {
        return false;
      }
      const formData = new FormData();
      formData.append('randFile', this.randFile);
      formData.append('password', this.password);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/importAsv', formData)
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.item = response.data;
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

    },


    /*
    handlerPage(page = 'list') {
      this.message = false;
      var pageWrapper = document.querySelector('#pageWrapper')
      if (pageWrapper) {
        pageWrapper.scrollTo(0, 0);
      }
      this.page = page;
    },
    */


  }
}
</script>

<style>

</style>
