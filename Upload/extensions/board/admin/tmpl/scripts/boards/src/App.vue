<template>

  <div>

    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>
    
    <ListComponent v-if="page === 'list'" :acl="acl" :list="list" ></ListComponent>
    <ItemComponent v-if="page === 'item'" :acl="acl" :item="item" ></ItemComponent>

    <EditComponent v-if="page === 'edit'" :acl="acl" :item="item" ></EditComponent>
    <EditFormComponent v-if="page === 'editform'" :acl="acl" :board="item" :edit="edit"  ></EditFormComponent>


  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

import ListComponent from './components/ListComponent.vue'
import ItemComponent from './components/ItemComponent.vue'
import EditComponent from './components/EditComponent.vue'
import EditFormComponent from './components/EditFormComponent.vue'

const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    AjaxError, AjaxSpinner,
    ListComponent, ItemComponent, EditComponent, EditFormComponent
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
      items: [],
      edit: false

    };
  },
  created() {
    this.loadList();

    this.$bus.$on('page--editform', data => {
      if (data.item) {
        this.edit = data.item;
      }
      this.handlerPage(data.page);
    });

    this.$bus.$on('page--edit', data => {
      if (data.item) {
        this.item = data.item;
      } else {
        this.item = {
          id: 0
        };
      }
      this.handlerPage(data.page);
    });

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



    this.$bus.$on('item--sort', data => {

      if (!data.items) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('items', JSON.stringify(data.items));

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setBoardsSort', formData)
          .then(function (response) {
            if (response.data) {

              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {

                that.loadList();
                //that.$bus.$emit('page--open', {'page':'list'});

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



    this.$bus.$on('editform--submit', data => {

      if (!data.title || !data.board_id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.id|| '');
      formData.append('board_id', data.board_id || '');
      formData.append('title', data.title|| '');
      formData.append('state', data.state || '');
      formData.append('text', data.text || '');
      formData.append('pdf', data.pdf || '');
      formData.append('cover', data.cover || '');
      formData.append('enddate', data.enddate || '');
      formData.append('url', data.url || '');


      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setItem', formData)
          .then(function (response) {
            if (response.data) {

              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {

                that.loadList();
                //that.$bus.$emit('page--open', {'page':'list'});
                that.$bus.$emit('page--edit', {
                  page: 'edit',
                  item: that.item
                });

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

    this.$bus.$on('item--submit', data => {

      if (!data.title) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.id|| '');
      formData.append('title', data.title|| '');
      formData.append('state', data.state || '');
      formData.append('cat_id', data.cat_id || '');

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setBoard', formData)
      .then(function (response) {
        if (response.data) {

          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {

            that.loadList();
            that.$bus.$emit('page--open', {'page':'list'});

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



    this.$bus.$on('editform--delete', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/delItem', formData)
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

    this.$bus.$on('item--delete', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/delBoard', formData)
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
      axios.get(this.apiURL + '/getBoards')
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
