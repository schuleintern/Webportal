<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>


    <div class="flex-row">
      <div class="flex-1 si-details margin-r-l">

        <ul>
          <li><h4><i class="fa fa-chart-line"></i> Übersicht</h4></li>
          <li>
            <label>Anzahl Fächer</label>
            {{ anzFaecher }}
          </li>
          <li>
            <label>Anzahl Unterrichte</label>
            {{ anzUnterrichte }}
          </li>

        </ul>
      </div>
      <div class="flex-1 ">

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

      anzFaecher: window.globals.anzFaecher,
      anzUnterrichte: window.globals.anzUnterrichte,

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
