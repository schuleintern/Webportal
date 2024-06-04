<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>
    <AjaxNotif></AjaxNotif>

    <ListComponent v-if="page === 'list'" :acl="acl" :list="list" ></ListComponent>


  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'
import AjaxNotif from './mixins/AjaxNotif.vue'

import ListComponent from './components/ListComponent.vue'

const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    AjaxError, AjaxSpinner,AjaxNotif,
    ListComponent
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      restURL: window.globals.restURL,
      acl: window.globals.acl,
      error: false,
      loading: false,
      page: 'list',

      list: []

    };
  },
  created() {
    //console.log(window.globals);

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

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setChangeUserList', formData)
          .then(function (response) {
            if (response.data) {

              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {

                that.$bus.$emit('notif--open',{
                  msg: 'Hinzugefügt'
                });

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
      axios.post(this.apiURL + '/deleteChangeUserList', formData)
          .then(function (response) {
            if (response.data) {

              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {

                that.$bus.$emit('notif--open',{
                  msg: 'Gelöscht'
                });

                that.loadList();
                if (data.callback) {
                  data.callback(response.data);
                }



              }
            } else {
              that.error = 'Fehler beim Löschen. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Löschen. 02';
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
      axios.get(this.apiURL + '/getChangeUserList')
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
