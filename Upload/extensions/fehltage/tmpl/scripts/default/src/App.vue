<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>


    <ListComponent v-if="page === 'list'" :acl="acl" :list="list" :slots="slots" ></ListComponent>
    <ItemComponent v-if="page === 'item'" :acl="acl" :item="item" ></ItemComponent>
    <FormComponent v-if="page === 'form'" :acl="acl" :item="item" ></FormComponent>


  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

import ListComponent from './components/ListComponent.vue'
import ItemComponent from './components/ItemComponent.vue'
import FormComponent from './components/FormComponent.vue'

const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    AjaxError, AjaxSpinner,
    ListComponent, ItemComponent, FormComponent
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

      slots: false,

    };
  },
  created() {
    this.loadList();
    this.loadSlots();


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

      let users = data.item.users.map((obj) => { return obj.id});

      if (!users || users.length < 1) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('title', data.item.title);
      formData.append('payee', data.item.payee);
      formData.append('users', users);
      formData.append('amount', data.item.amount_1+'.'+data.item.amount_2);
      formData.append('dueDate', data.item.dueDate);
      formData.append('receipt', data.item.receipt);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setAntrag', formData)
      .then(function (response) {
        if (response.data) {

          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {

            that.loadList();
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


    this.$bus.$on('item--delete', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/deleteAdminKalender', formData)
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
      axios.get(this.apiURL + '/getItems')
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

    loadSlots() {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getSlots')
      .then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            that.slots = response.data;
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
