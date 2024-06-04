<template>
  <div>

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <div class="si-hinweis" v-if="LANG_myslots_hinweis" v-html="LANG_myslots_hinweis"></div>

    <table class="si-table">
      <thead>
        <tr>
          <td>Status</td>
          <td>Jahrgangsstufe</td>
          <td>Fach</td>
          <td>Stunden</td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <tr v-if="list && list.length >= 1" v-bind:key="index" v-for="(item, index) in  list"
          class="">
          <td>
            <i v-if="item.status == 'closed'" class="fas fa-times-circle text-red"></i>
            <i v-if="item.status == 'open'" class="fas fa-check-circle text-green"></i>
            <i v-if="item.status == 'created'" class="fas fa-check-circle text-orange"></i>
          </td>
          <td>{{item.jahrgang}}</td>
          <td>{{item.fach}}</td>
          <td>noch {{item.diff}} von {{item.einheiten}}</td>
          <td><Item v-if="item.slots" v-bind:data="item.slots"></Item></td>
        </tr>
        <tr v-if="list.length == 0">
          <td colspan="5"> - keine Inhalte vorhanden -</td>
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
    Error, Spinner, User, Item
  },
  data() {
    return {
      apiURL: globals.apiURL,
      error: false,
      loading: false,

      list: false, // from AJAX,

      LANG_myslots_hinweis: globals.LANG_myslots_hinweis

    };
  },
  created: function () {

    this.loadList();

  },
  mounted() {

    EventBus.$on('form-submit', data => {

      const formData = new FormData();
      formData.append('id', data.data.id);
      //formData.append('dauer', data.data.dauer);
      //formData.append('datum', data.data.datum);
      formData.append('info', data.data.info);
      formData.append('dates', data.data.dates);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/endSlot', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          //that.list = response.data;
          //console.log(response.data.error);
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

    });

  },
  methods: {

    loadList: function () {

      this.loading = true;
      var that = this;
      axios.get( this.apiURL+'/getMySlots')
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

    }
  }

};
</script>

<style>

</style>