<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>


    <ul class="noListStyle padding-t-l form-style-2">
      <li class="padding-t-m padding-b-m line-oddEven">
        <div class="flex-row">
          <div class="flex-1 padding-r-l flex flex-center-center">
            Push-Nachrichten aktivieren
          </div>
          <div class="flex-2">

            <FormToggle :disable="1" :input="pushActive" @change="handlerTogglePush"></FormToggle>
          </div>

        </div>

      </li>
    </ul>
  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'


import FormToggle from './mixins/FormToggle.vue'


const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    FormToggle,
    AjaxError, AjaxSpinner
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      acl: window.globals.acl,
      error: false,
      loading: false,
      page: 'list',

      pushActive: window.globals.pushActive


    };
  },
  created() {

    this.$bus.$on('page--open', data => {
      if (data.item) {
        this.item = data.item;
      } else {
        this.item = {
          id: 0,
          title: ''
        };
      }
      this.handlerPage(data.page);
    });

  },
  methods: {

    handlerTogglePush(data) {

      const formData = new FormData();
      formData.append('value', data.value);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + 'setPushActive', formData)
          .then(function (response) {
            if (response.data) {

              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {

                that.pushActive = response.data.active;

              }
            } else {
              that.error = 'Fehler beim Speichern. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Speichern. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });

    },

    handlerPage(page = 'list') {
      this.page = page;
    },

  }
}
</script>

<style>

</style>
