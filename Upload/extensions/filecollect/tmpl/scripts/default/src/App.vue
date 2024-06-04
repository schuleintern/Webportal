<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <List v-show="page == 'list' && acl.read == 1" v-bind:acl="acl" v-bind:items="items"></List>


  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

import List from './components/List.vue'

const axios = require('axios').default;


export default {
  setup() {

  },
  name: 'App',
  components: {
    AjaxError, AjaxSpinner, List
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      acl: window.globals.acl,
      error: false,
      loading: false,
      page: 'list',
      items: []
    };
  },

  created() {

    this.loadList();


    this.$bus.$on('form--upload', data => {

      if (!data.files || !data.folderid || !data.maxfiles) {
        console.log('missing');
        return false;
      }

      if (data.files.length > data.maxfiles) {
        this.error = 'Zu viele Daten ausgewählt.';
        return false;
      }

      const formData = new FormData();
      formData.append('folderid', data.folderid);
      var ins = data.files.length;
      for (var x = 0; x < ins; x++) {
        formData.append('files[]', data.files[x]);
      }

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setUpload', formData)
          .then(function (response) {
            if (response.data) {

              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.loadList();
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

    });


  },
  methods: {
    handlerSort: function (column) {
      if (column) {
        this.sort.column = column;
        if (this.sort.order) {
          this.sort.order = false;
        } else {
          this.sort.order = true;
        }
      }
    },
    loadList() {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getMyFolders')
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.items = response.data;
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
}
</script>

<style>

</style>
