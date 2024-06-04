<template>
  <div>

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <Modal v-bind:data="selectedItem"></Modal>


    <div class="si-hinweis" v-if="LANG_disclaimer" v-html="LANG_disclaimer"></div>

    <table class="si-table">
      <thead>
        <tr>
          <td></td>
          <td>Jahrgangsstufe</td>
          <td>Fach</td>
          <td>Stunden</td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <tr v-if="list && list.length >= 1" v-bind:key="index" v-for="(item, index) in  list"
          class="">
          <td><User v-bind:data="item.user"></User></td>
          <td>{{item.jahrgang}}</td>
          <td>{{item.fach}}</td>
          <td>{{item.diff}}</td>
          <td><button class="si-btn" v-on:click="handlerShow(item)"><i class="fa fa-file"></i>Anzeigen</button></td>
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
import Modal from './mixins/Modal.vue'



export default {
  components: {
    Error, Spinner, User, Modal
  },
  data() {
    return {
      apiURL: globals.apiURL,
      error: false,
      loading: false,

      list: false, // from AJAX

      selectedItem: false,

      LANG_disclaimer: globals.LANG_disclaimer
    };
  },
  created: function () {

    this.loadList();

  },
  mounted() {

    EventBus.$on('item-submit', data => {
      //console.log(data)

      const formData = new FormData();
      formData.append('id', data.id);
      formData.append('einheiten', data.einheiten);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/orderSlot', formData, {
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
              that.selectedItem = false;
              if (data.tutorUserID) {
                window.location = "index.php?page=MessageCompose&recipient=U:"+data.tutorUserID;
              }

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
      axios.get( this.apiURL+'/getList')
      .then(function(response){
        if ( response.data ) {
          if ( response.data.error ) {
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

    handlerShow: function (item) {
      this.selectedItem = false; // Bugfix wegen: watch
      this.selectedItem = item;
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