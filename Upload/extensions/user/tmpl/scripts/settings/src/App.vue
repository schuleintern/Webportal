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
    


    /*
    this.$bus.$on('item--submit', data => {

      if (!data.item.image || !data.item.user_id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('user_id', data.item.user_id);
      formData.append('image', data.item.image);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setAntrag', formData)
        .then(function (response) {
          
          if (response && response.data) {

            if (response.data.error) {
              that.error = '' + response.data.msg;
            } else {

              that.handlerPage('item');
              that.loadAusweis();
              that.loadAntrag();
              
              if (data.callback) {
                data.callback(response.data);
              }

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
    */




  },
  methods: {

    loadData() {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getUser')
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
