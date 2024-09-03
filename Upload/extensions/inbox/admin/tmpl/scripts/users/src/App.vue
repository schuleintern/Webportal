<template>

  <div class="margin-t-m">
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <AjaxNotif></AjaxNotif>

    <ListComponent v-if="page === 'list'" :acl="acl" :list="list" ></ListComponent>
    <!--<ItemComponent v-if="page === 'item'" :acl="acl" :item="item" ></ItemComponent>-->
    <FormComponent v-if="page === 'form'" :acl="acl" :item="item" ></FormComponent>


  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'
import AjaxNotif from './mixins/AjaxNotif.vue'

import ListComponent from './components/ListComponent.vue'
//import ItemComponent from './components/ItemComponent.vue'

import FormComponent from './components/FormComponent.vue'

const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    AjaxError, AjaxSpinner, AjaxNotif,
    ListComponent, FormComponent
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


      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('isPublic', JSON.stringify(data.item.isPublic));

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setUsersAdmin', formData)
      .then(function (response) {
        if (response.data) {

          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {

            that.loadList();
            if (data.callback) {
              data.callback(response.data);
            }
            that.handlerPage('list');

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


    this.$bus.$on('page--makeUser', () => {



      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/makeUsersAdmin')
      .then(function (response) {
        if (response.data) {

          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {

            that.$bus.$emit('notif--open',{
              msg: 'Es wurden '+response.data.count+' Accounts erstellt.'
            })
            that.loadList();
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      })
      .catch(function (e) {
        console.log(e);
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
      axios.get(this.apiURL + '/getUsersAdmin')
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
