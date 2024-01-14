<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>


    <ListComponent v-if="page === 'list'" :acl="acl" :list="list"></ListComponent>
    <ItemComponent v-if="page === 'item'" :acl="acl" :item="item"></ItemComponent>


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


    this.$bus.$on('item--add-holiday', () => {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/setAdminFerien')
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
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

    this.$bus.$on('item--sort', data => {

      if (!data.items) {
        console.log('missing');
        return false;
      }

      let arr = [];
      let i = 1;
      data.items.forEach((item)=> {
        arr.push({
          'id': item.id,
          'sort': i
        });
        i++;
      })
      const formData = new FormData();
      formData.append('items', JSON.stringify(arr) );

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setAdminKalenderSort', formData)
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
            that.loading = false;
          });

    });

    this.$bus.$on('item--state', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }
      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('state', data.item.state);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setAdminKalenderState', formData)
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {

                //console.log('DONE');

              }
            } else {
              that.error = 'Fehler beim Speichern. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Speichern. 02';
          })
          .finally(function () {
            that.loading = false;
          });

    });

    this.$bus.$on('item--submit', data => {

      if (!data.item.title) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('title', data.item.title);
      formData.append('state', data.item.state);
      formData.append('color', data.item.color);
      formData.append('sort', data.item.sort);
      formData.append('preSelect', data.item.preSelect);
      formData.append('acl', data.item.acl);
      formData.append('ferien', data.item.ferien);
      formData.append('public', data.item.public);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setAdminKalender', formData)
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

    this.$bus.$on('item--acl', data => {

      if (!data.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('acl', JSON.stringify(data.acl));
      formData.append('id', data.id);


      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setAdminKalenderAcl', formData)
          .then(function (response) {
            if (response.data) {

              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                //console.log('done');
                that.loadList();
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
      axios.get(this.apiURL + '/getAdminKalenders')
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
