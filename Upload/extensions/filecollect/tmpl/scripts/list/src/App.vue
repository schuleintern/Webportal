<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <List v-show="page == 'list' && acl.read == 1" v-bind:acl="acl" v-bind:items="items"></List>
    <Item v-if="page == 'item' && acl.read == 1" v-bind:acl="acl" v-bind:item="item"></Item>
    <Form v-show="page == 'form' && acl.write == 1" v-bind:acl="acl" v-bind:item="form"></Form>

  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

import Item from './components/Item.vue'
import List from './components/List.vue'
import Form from './components/Form.vue'

const axios = require('axios').default;


export default {
  setup() {

  },
  name: 'App',
  components: {
    AjaxError, AjaxSpinner, List, Item, Form
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      acl: window.globals.acl,
      error: false,
      loading: false,
      page: 'list',
      items: [],
      item: {},
      form: {}
    };
  },
  computed: {
    sortList: function () {
      if (this.list) {
        let data = this.list;
        if (data.length > 0) {

          // SUCHE
          if (this.searchString != '') {
            let split = this.searchString.toLowerCase().split(' ');
            var search_temp = [];
            var search_result = [];
            this.searchColumns.forEach(function (col) {
              search_temp = data.filter((item) => {
                return split.every(v => item[col].toLowerCase().includes(v));
              });
              if (search_temp.length > 0) {
                search_result = Object.assign(search_result, search_temp);
              }
            });
            data = search_result;
          }

          // SORTIERUNG
          if (this.sort.column) {
            if (this.sort.order) {
              return data.sort((a, b) => a[this.sort.column].localeCompare(b[this.sort.column]))
            } else {
              return data.sort((a, b) => b[this.sort.column].localeCompare(a[this.sort.column]))
            }
          }

          return data;
        }
      }
      return [];
    }

  },
  created() {

    this.loadList();

    this.$bus.$on('page--open', data => {

      if (data.back) {
        if (this.form.id) {
          if (this.item.id) {
            this.form = {};
            this.page = 'item';
          } else {
            this.item = {};
            this.form = {};
            this.page = 'list';
          }
          return false;
        }
        this.item = {};
        this.form = {};
        this.page = 'list';
        return false;
      }

      if (!data.page) {
        return false;
      }

      if (data.page == 'item') {
        if (data.item) {
          this.item = data.item;
        }
      }
      if (data.page == 'form') {
        if (data.item) {
          //this.item = data.item;
          this.form = data.item;
        }
      }
      if (data.page == 'list') {
        this.item = {};
      }
      this.page = data.page;
    });

    this.$bus.$on('form--submit', data => {

      if (!data.item.title) {
        console.log('missing');
        return false;
      }
      var endDate = 0;
      if (data.item.endDate) {
        endDate = new Date(data.item.endDate);
        endDate = endDate.getTime() / 1000
      }


      const formData = new FormData();
      formData.append('title', data.item.title);
      formData.append('members', JSON.stringify(data.item.members));
      formData.append('info', data.item.info || '');
      formData.append('id', data.item.id);
      formData.append('endDate', endDate);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setList', formData)
          .then(function (response) {

            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.loadList();
                that.$bus.$emit('page--open', {
                  back: true
                });
                /*
                if (data.callback) {
                  data.callback(response.data);
                }
                */
              }
            } else {
              that.error = 'Fehler beim Speichern. 01';
            }

          })
          .catch(function (a, b) {
            console.log(a, b);
            that.error = 'Fehler beim Speichern. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });

    });

  },
  methods: {
    handlerSort: function (column) {
      if (column) {
        this.sort.column = column;
        if (this.sort.order) {
          this.sort.order = false;
        } else {
          this.sort.order = true;
        }
      }
    },
    loadList() {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getList')
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.items = response.data;
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
    handlerSaveForm(e) {

      e.preventDefault();

      if (this.form.stunden.length < 1 && this.form.schueler && this.form.date) {
        return false;
      }

      const formData = new FormData();
      formData.append('stunden', this.form.stunden);
      formData.append('schueler', this.form.schueler);
      formData.append('date', this.form.date);
      formData.append('info', this.form.info);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setAntrag', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
          .then(function (response) {
            if (response.data) {

              if (response.data.error == true) {
                that.error = '' + response.data.msg;
              } else {
                that.loadList();
                that.handlerPage();
                //data.item.favorite = response.data.favorite;
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


    }

  }
}
</script>

<style scoped>

</style>