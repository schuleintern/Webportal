<template>
  <div>
    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <List v-show="tabOpen == 'list' && acl.read == 1" v-bind:items="items" v-bind:unsigned="unsigned" v-bind:acl="acl"
      v-bind:groups="groups"></List>
    <Item v-if="tabOpen == 'item' && acl.read == 1" v-bind:item="item" v-bind:acl="acl" v-bind:groups="groups"
      v-bind:showDays="showDays" v-bind:beurlaubung="beurlaubung" v-bind:absenz="absenz"></Item>



    <ModalUnsigned v-bind:unsigned="unsigned"></ModalUnsigned>

  </div>
</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'

import ModalUnsigned from './mixins/ModalUnsigned.vue'
import Item from './components/Item.vue'
import List from './components/List.vue'


export default {
  components: {
    Error, Spinner, List, Item, ModalUnsigned
  },
  data() {
    return {
      apiURL: globals.apiURL,
      acl: globals.acl,
      showDays: globals.showDays,

      loading: false,
      error: false,

      unsigned: {},
      items: [],
      item: {
        members: [],
        owners: []
      },
      groups: [],
      beurlaubung: [],
      absenz: [],

      tabOpen: 'list'

    };
  },
  created: function () {

    this.loadLists();
    this.loadUnsigned();
    this.loadGroups();

    EventBus.$on('tab--open', data => {
      if (!data.tabOpen) {
        return false;
      }

      if (data.tabOpen == 'list') {
        this.item = {
          members: [],
          owners: []
        };
      }


      this.tabOpen = data.tabOpen;
    });


  },
  mounted() {

    var that = this;




    EventBus.$on('item--open', data => {

      if (data.item) {
        that.item = data.item;
        that.tabOpen = 'item';

        that.beurlaubung = [];
        that.loadBeurlaubungsanttrag();

        that.absenz = [];
        that.loadAbsenz();
      }
    });


    EventBus.$on('unsigned--merge', data => {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/setUnsigned')
        .then(function (response) {
          if (response.data) {
            if (!response.data.error) {

              that.unsigned = response.data;
              this.loadLists();

            } else {
              that.error = '' + response.data.msg;
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


    EventBus.$on('item--change', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('days', JSON.stringify(data.item.days));
      formData.append('info', data.item.info);
      formData.append('anz', data.item.anz);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setSchueler', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
        .then(function (response) {
          if (response.data) {

            if (response.data.error) {
              that.error = '' + response.data.msg;
            } else {
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

    EventBus.$on('absenz--del', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/deleteAbsenzAdmin', formData)
        .then(function (response) {
          if (response.data) {

            if (response.data.error) {
              that.error = '' + response.data.msg;
            } else {
              if (data.callback) {
                data.callback(response.data);
              }
              that.absenz = [];
              that.loadAbsenz();
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

    loadAbsenz: function () {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getAbsenz/' + this.item.user_id)
        .then(function (response) {
          if (response.data) {
            if (!response.data.error) {

              that.absenz = response.data;

            } else {
              that.error = '' + response.data.msg;
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
    loadBeurlaubungsanttrag: function () {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getBeurlaubungsantraege/' + this.item.user_id)
        .then(function (response) {
          if (response.data) {
            if (!response.data.error) {

              that.beurlaubung = response.data;

            } else {
              that.error = '' + response.data.msg;
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
    loadLists: function () {
      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getSchueler')
        .then(function (response) {
          if (response.data) {
            if (!response.data.error) {

              that.items = response.data;

            } else {
              that.error = '' + response.data.msg;
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
    loadUnsigned: function () {
      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getUnsigned')
        .then(function (response) {
          if (response.data) {
            if (!response.data.error) {

              that.unsigned = response.data;

            } else {
              that.error = '' + response.data.msg;
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
    loadGroups: function () {
      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getGroups')
        .then(function (response) {
          if (response.data) {
            if (!response.data.error) {

              that.groups = response.data;

            } else {
              that.error = '' + response.data.msg;
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

  }

};
</script>

<style></style>