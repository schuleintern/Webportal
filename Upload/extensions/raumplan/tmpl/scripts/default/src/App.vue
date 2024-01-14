<template>
  <div>

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <div class="si-btn-multiple padding-b-m">
      <button v-bind:key="i" v-for="(raum, i) in rooms"
              v-on:click="handlerChangeRoom(raum)"
              class="si-btn si-btn-light margin-r-s"
              :class="{ 'si-btn-active':  room == raum}">{{raum}}</button>
    </div>

    <Calendar v-bind:plan="plan" v-bind:showDays="showDays" v-bind:room="room" ></Calendar>

  </div>
</template>

<script>

const axios = require('axios').default;

import User from './mixins/User.vue'
import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'

import Calendar from './components/Calendar.vue'

export default {
  components: {
    Error, Spinner, User, Calendar
  },
  data() {
    return {
      apiURL: globals.apiURL,

      loading: false,
      error: false,
      plan: [],

      acl: globals.acl,
      settingsRooms: globals.settingsRooms,
      room: globals.room,
      showDays: globals.showDays,
      rooms: globals.rooms

    };
  },
  created: function () {


    this.rooms = JSON.parse(this.rooms);

    var that = this;

    EventBus.$on('calendar--changedDate', data => {

      this.showFirstDayWeek = data.von;
      this.showLastDayWeek = data.bis;

      if (data.room) {
        this.room = data.room;
      }

      this.loadList();

    });

  },
  mounted() {

    var that = this;


    EventBus.$on('form--cancel', data => {

      if (!data.unit.id
          || !data.unit.createdBy) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.unit.id);
      formData.append('createdBy', data.unit.createdBy);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/cancelSlot', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
          .then(function(response){
            if ( response.data ) {
              if (response.data.error == false) {

                EventBus.$emit('calender--reload', {});

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


    EventBus.$on('form--submit', data => {

      if (!data.form.date
          || !data.form.stunde
          || !data.form.room
          || !data.form.klasse
          || !data.form.lehrer
          || !data.form.fach) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('stunde', data.form.stunde);
      formData.append('room', data.form.room);
      formData.append('klasse', data.form.klasse);
      formData.append('lehrer', data.form.lehrer);
      formData.append('fach', data.form.fach);
      formData.append('date', data.form.date);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/saveSlot', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error == false) {

            EventBus.$emit('calender--reload', {});

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

    handlerChangeRoom : function (room) {

      EventBus.$emit('calendar--changedDate', {
        von: this.showFirstDayWeek,
        bis: this.showLastDayWeek,
        room: room
      });

    },
    loadList: function () {

      this.loading = true;
      var that = this;
      const params = new URLSearchParams();
      params.append('von', this.showFirstDayWeek);
      params.append('bis', this.showLastDayWeek);
      params.append('room', this.room);
      axios.get( this.apiURL+'/getWeek',{
            params: params
      })
      .then(function(response){
        if ( response.data ) {
          if (!response.data.error) {
            that.plan = response.data;
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