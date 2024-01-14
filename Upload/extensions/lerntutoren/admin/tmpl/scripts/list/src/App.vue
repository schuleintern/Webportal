<template>
  <div>

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>


    <table class="si-table">
      <thead>
        <tr>
          <td></td>
          <td></td>
          <td>Jahrgangsstufe</td>
          <td>Fach</td>
          <td>Stunden</td>
          <td></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <tr v-if="list && list.length >= 1" v-bind:key="index" v-for="(item, index) in  list"
          class="">
          <td><User v-if="item.user" v-bind:data="item.user"></User></td>
          <td>
            <i v-if="item.status == 'closed'" class="fas fa-times-circle text-red"></i>
            <i v-if="item.status == 'open'" class="fas fa-check-circle text-green"></i>
            <i v-if="item.status == 'created'" class="fas fa-check-circle text-orange"></i>
          </td>
          <td>{{item.jahrgang}}</td>
          <td>{{item.fach}}</td>
          <td>noch {{item.diff}} von {{item.einheiten}}</td>

          <td><Item @closeSlot="handlerCloseSlot" v-if="item.slots" v-bind:data="item.slots"></Item></td>
          <td>
            <button v-if="item.status == 'created'" class="si-btn si-btn-green" v-on:click="handlerFreigeben(item)"><i class="fas fa-unlock"></i> Freigeben</button>
            <button v-if="item.status != 'closed'" class="si-btn si-btn-light" v-on:click="handlerClose(item)"><i class="fas fa-power-off"></i>Abbrechen</button>
          </td>

        </tr>
        <tr v-if="list.length == 0">
          <td colspan="7"> - keine Inhalte vorhanden -</td>
        </tr>

      </tbody>
    </table>

  </div>
</template>

<script>

const axios = require('axios').default;

import User from './mixins/User.vue'
import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'

import Item from './components/Item.vue'



export default {
  components: {
    Error, Spinner, User,
    Item
  },
  data() {
    return {
      apiURL: globals.apiURL,
      error: false,
      loading: false,

      list: false, // from AJAX

    };
  },
  created: function () {

    this.loadList();

  },
  mounted() {

  },
  methods: {

    handlerCloseSlot: function (item) {

      const formData = new FormData();
      formData.append('id', item.id);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/closeSlotAdmin', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error == false) {
            that.loadList();
          } else {
            that.error = ''+response.data.msg;
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      })
      .catch(function(){
        that.error = 'Fehler beim Laden. 02';
      })
      .finally(function () {
        // always executed
        that.loading = false;
      });

    },
    handlerClose: function (item) {

      const formData = new FormData();
      formData.append('id', item.id);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/closeAdmin', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error == false) {
            that.loadList();
          } else {
            that.error = ''+response.data.msg;
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      })
      .catch(function(){
        that.error = 'Fehler beim Laden. 02';
      })
      .finally(function () {
        // always executed
        that.loading = false;
      });


    },
    loadList: function () {

      this.loading = true;
      var that = this;
      axios.get( this.apiURL+'/getListAdmin')
      .then(function(response){
        if ( response.data ) {
          if (response.data.error) {
            that.error = ''+response.data.msg;
          } else {
            that.list = response.data;
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      })
      .catch(function(){
        that.error = 'Fehler beim Laden. 02';
      })
      .finally(function () {
        // always executed
        that.loading = false;
      });

    },

    handlerFreigeben: function (item) {

      const formData = new FormData();
      formData.append('id', item.id);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/openAdmin', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error == false) {
            that.loadList();
          } else {
            that.error = ''+response.data.msg;
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      })
      .catch(function(){
        that.error = 'Fehler beim Laden. 02';
      })
      .finally(function () {
        // always executed
        that.loading = false;
      });

    }
  }

};
</script>

<style>
#app {
  font-size: 18px;
  font-family: 'Roboto', sans-serif;
  color: blue;
}
</style>