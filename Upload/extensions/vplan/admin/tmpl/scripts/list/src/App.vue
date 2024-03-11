<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>


    <ItemComponent v-if="page === 'item'" :acl="acl" :item="item" :randFolder="randFolder"></ItemComponent>
    <ListComponent v-if="page === 'list'" :acl="acl" :list="list" :showCol="showCol" ></ListComponent>


  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

import ItemComponent from './components/ItemComponent.vue'
import ListComponent from './components/ListComponent.vue'

const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    ListComponent, ItemComponent,
    AjaxError, AjaxSpinner
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

      showCol: window.globals.showCol

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



    this.$bus.$on('item--setDelete', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/delEvent', formData)
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

    this.$bus.$on('item--setStatus', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setAdminEventsStatus', formData)
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

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getAdminList')
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
