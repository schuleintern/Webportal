<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>


    <ListComponent v-if="page === 'list'" :acl="acl" :list="list" :count="count" ></ListComponent>
    <ItemComponent v-if="page === 'item'" :acl="acl"  ></ItemComponent>
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
      count: window.globals.count,
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
        //console.log(data.item);
        this.item = data.item;
      } else {
        this.item = {
          id: 0
        };
      }
      this.handlerPage(data.page);
    });

    this.$bus.$on('item--submit', data => {

      if (!data.item.user || !data.item.user.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('user_id', data.item.user.id);
      if (data.item.by.id) {
        formData.append('by', data.item.by.id);
      }
      formData.append('info', data.item.info);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setUser', formData)
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


    this.$bus.$on('item--submit-form', data => {

      console.log(data);

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('count', data.item.count);
      formData.append('typ', data.item.typ);
      if (data.form.by.id) {
        formData.append('by', data.form.by.id);
      }
      formData.append('info', data.form.info);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setCount', formData)
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

    this.$bus.$on('item--delete-count', data => {


      console.log(data);

      if (!data.item.id || !data.type) {
        console.log('missing');
        return false;
      }

      if (data.type == 'create' && parseInt(data.item.count) == 1) {
        data.type = 'all';

      }
      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('type', data.type);
      formData.append('parent_id', data.item.parent_id);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/deleteCount', formData)
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



  },
  methods: {

    loadList() {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getUsers')
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
