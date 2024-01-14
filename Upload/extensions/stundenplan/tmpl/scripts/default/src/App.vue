<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>


    <ListComponent v-if="page === 'list'" :acl="acl" :list="list" :defaults="defaults"></ListComponent>
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
      item: [],
      defaults: false

    };
  },
  created() {
    this.loadList();
    this.loadDefaults();


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


    this.$bus.$on('item--load', data => {

      this.loadList(data.item, data.callback);
      return false;

    });




  },
  methods: {

    loadDefaults() {

      let sessionID = localStorage.getItem('session')
      if (sessionID) { sessionID = sessionID.replace('__q_strn|',''); }

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getDefault', {
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
            that.defaults = response.data;
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
    loadList(item, callback) {

      const formData = new FormData();

      if (item) {

        //console.log(item);
        
        formData.append('key', item[1]);
        if ( item[1] == 'teacher' && item[2]) {
          formData.append('value', item[2]);
        } else {
          formData.append('value', item[0]);
        }
      }

      let sessionID = localStorage.getItem('session')
      if (sessionID) { sessionID = sessionID.replace('__q_strn|',''); }

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/getStundenplan', formData, {
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
            if (callback && typeof callback == 'function') {
              callback();
            }
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
