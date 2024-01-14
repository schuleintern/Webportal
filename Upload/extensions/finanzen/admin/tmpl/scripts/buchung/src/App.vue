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
    ListComponent, ItemComponent, 
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

    this.$bus.$on('action-rechnung', () => {
      
      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getMakeRechnungenAdmin')
      .then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            console.log(response.data);
            that.loadList();
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

    });

    


  },
  methods: {

    loadList() {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getBuchungAdmin')
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
