<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>


    <ListComponent v-if="page === 'list'" :acl="acl" :list="list" ></ListComponent>
    <ItemComponent v-if="page === 'item'" :acl="acl" :item="item" ></ItemComponent>


  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

import ListComponent from './components/ListComponent.vue'
import ItemComponent from './components/ItemComponent.vue'

const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    AjaxError, AjaxSpinner,
    ListComponent, ItemComponent
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      acl: window.globals.acl,
      error: false,
      loading: false,
      page: 'list',

      list: false,
      item: []

    };
  },
  created() {
    this.loadList();


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


    this.$bus.$on('item--submit', data => {

      if (!data.item.title) {
        console.log('missing');
        return false;
      }

      let sessionID = localStorage.getItem('session');
      if (sessionID) {
        sessionID = sessionID.replace('__q_strn|','');
      }

      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('title', data.item.title);
      formData.append('short', data.item.short || '');
      formData.append('isPublic', data.item.isPublic || 0);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setLnws', formData , {
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

            that.loadList();
            if (data.callback) {
              data.callback(response.data);
            }
            that.handlerPage();

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


    this.$bus.$on('item--delete', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      let sessionID = localStorage.getItem('session');
      if (sessionID) {
        sessionID = sessionID.replace('__q_strn|','');
      }

      const formData = new FormData();
      formData.append('id', data.item.id);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/deleteLnws', formData, {
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
            that.loadList();
            that.handlerPage();
          }
        } else {
          that.error = 'Fehler beim Speichern. 01';
        }
      })
      .catch(function (e) {
        console.log(e);
        that.error = 'Fehler beim Speichern. 02';
      })
      .finally(function () {
        // always executed
        that.loading = false;
      });

    });


  },
  methods: {

    loadList() {

      let sessionID = localStorage.getItem('session');
      if (sessionID) {
        sessionID = sessionID.replace('__q_strn|','');
      }

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getLnws', {
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
            that.list = response.data;
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

    handlerPage(page = 'list') {
      this.page = page;
    },

  }
}
</script>

<style>

</style>
