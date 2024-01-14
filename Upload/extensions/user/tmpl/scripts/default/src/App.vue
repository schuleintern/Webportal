<template>
  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <ItemComponent v-if="page === 'item'" :acl="acl" :item="item"></ItemComponent>

  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

import ItemComponent from './components/ItemComponent.vue'

const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    AjaxError, AjaxSpinner,
    ItemComponent
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      acl: window.globals.acl,
      error: false,
      loading: false,
      page: 'item',

      item: {}

    };
  },
  created() {


    this.loadData();


    this.$bus.$on('page--open', data => {
      if (data.item) {
        this.item = data.item;
      } else {
        this.item = {
          id: 0
        };
      }
      this.handlerPage(data.page);
    });




  },
  methods: {

    loadData() {

      let sessionID = localStorage.getItem('session');
      if (sessionID) {
        sessionID = sessionID.replace('__q_strn|','');
      }
      

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getUser', {
        headers: {
          'auth-app': window.globals.apiKey,
          'auth-session': sessionID
        }
      })
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

    handlerPage(page = 'item') {
      this.page = page;
    },

  }
}
</script>

<style></style>
