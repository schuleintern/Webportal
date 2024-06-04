<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>


    <div class="flex-row">
      <div class="flex-1 si-details margin-r-l">

        <ul>
          <li><h4><i class="fa fa-chart-line"></i> Übersicht</h4></li>
          <li>
            <label>Anzahl Lehrer*innen</label>
            {{ lehrerAnzahl }}
          </li>
          <li class="text-red" v-if="unsetLehrer">
            <label>Neue Lehrer*innen</label>
            <a class="si-btn si-btn-red" href="index.php?page=ext_users&view=default&admin=true&task=createTeacher"><i class="fa fa-plus"></i> {{ unsetLehrer }}
              Benutzer anlegen
            </a>
          </li>
          <li>
            <label>Anzahl Schüler*innen</label>
            {{ schuelerAnzahl }}
          </li>
          <li class="text-red" v-if="unsetSchueler">
            <label>Neue Schüler*innen</label>
            <a class="si-btn si-btn-red" href="index.php?page=ext_users&view=default&admin=true&task=createTeacher"><i class="fa fa-plus"></i> {{ unsetSchueler }}
              Benutzer anlegen
            </a>
          </li>
          <li class="text-red" v-if="unsetEltern">
            <label>Neue Eltern</label>
            <a class="si-btn si-btn-red" href="index.php?page=ext_users&view=default&admin=true&task=createTeacher"><i class="fa fa-plus"></i> {{ unsetEltern }}
              Benutzer anlegen
            </a>
          </li>
        </ul>
      </div>
      <div class="flex-1 si-details">
        <ul>
          <li><h4><i class="fa fa-user-cog"></i> Einstellungen</h4></li>
          <li>
            <label>Lehrer*innen</label>
            {{ lehrerUserMode }}
            <div class="text-small text-grey margin-t-s">
              SYNC: Synchronisierung
              <br>
              ASV: Benutzer kommen aus der ASV
            </div>
          </li>
          <li>
            <label>Schüler*innen</label>
            {{ schuelerUserMode }}
            <div class="text-small text-grey margin-t-s">
              SYNC: Synchronisierung
              <br>
              ASV: Benutzer kommen aus der ASV
            </div>
          </li>
          <li>
            <label>Eltern</label>
            {{ elternUserMode }}
            <div class="text-small text-grey margin-t-s">
              ASV_MAIL: E-Mailadressen kommen aus der ASV
              <br>
              ASV_CODE: Eltern bekommen Elternbrief mit Code zur Selbstregistrierung
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'


//const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    AjaxError, AjaxSpinner
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      acl: window.globals.acl,

      lehrerUserMode: window.globals.lehrerUserMode,
      schuelerUserMode: window.globals.schuelerUserMode,
      elternUserMode: window.globals.elternUserMode,
      lehrerAnzahl: window.globals.lehrerAnzahl,
      unsetLehrer: window.globals.unsetLehrer,
      schuelerAnzahl: window.globals.schuelerAnzahl,
      unsetSchueler: window.globals.unsetSchueler,
      unsetEltern: window.globals.unsetEltern,

      error: false,
      loading: false


    };
  },
  created() {

  },
  methods: {

  }
}
</script>

<style>

</style>
