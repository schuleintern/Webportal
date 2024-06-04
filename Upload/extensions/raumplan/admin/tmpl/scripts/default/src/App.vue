<template>
  <div>

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <div class="si-hinweis">Hier können sie entscheiden, welche Räume über das Modul buchbar sind.</div>

    <ul v-if="rooms" class="noListStyle padding-t-l form-style-2">
      <li v-bind:key="i" v-for="(room, i) in rooms" class="flex-row padding-t-s padding-b-s line-oddEven">
        <div class="flex-1 padding-l-l">{{room.name}}</div>
        <div class="flex-2">
          <button
              v-if="room.checked == 1"
              v-on:click="handlerToggleRoom(room)"
              class="btn text-green"><i class="fa fas fa-toggle-on"></i> An</button>
          <button
              v-else-if="room.checked == 0 || room.checked == undefined"
              v-on:click="handlerToggleRoom(room)"
              class="btn"><i class="fa fas fa-toggle-off"></i> Aus</button>

        </div>
      </li>
    </ul>

  </div>
</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'



export default {
  components: {
    Error, Spinner
  },
  data() {
    return {
      apiURL: globals.apiURL,

      loading: false,
      error: false,

      rooms: false

    };
  },
  created: function () {

    this.loadData();

  },
  mounted() {

  },
  methods: {

    handlerToggleRoom: function (room) {

      if (room.checked) {
        room.checked = false;
      } else {
        room.checked = true;
      }

      const formData = new FormData();
      formData.append('rooms', JSON.stringify(this.rooms) );

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/postRoomsAdmin', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error == false) {

            //console.log(response.data);

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
    loadData: function () {

      this.loading = true;
      var that = this;

      axios.get( this.apiURL+'/getRoomsAdmin')
      .then(function(response){
        if ( response.data ) {
          if (!response.data.error) {
            that.rooms = response.data;
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

</style>