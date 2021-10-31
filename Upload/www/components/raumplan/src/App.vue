<template>

  <div id="app">


    <div v-show="error" class="form-modal-error">
      <b>Folgende Fehler sind aufgetreten:</b>
      <div>{{error}}</div>
    </div>

    <div v-if="loading == true" class="overlay">
      <i class="fa fas fa-sync-alt fa-spin"></i>
    </div>
    <div>
      <button v-bind:key="i" v-for="(raum, i) in settingsRooms"
              v-on:click="handlerChangeRoom(raum)"
              class="btn margin-r-s"
              :class="{ 'bg-orange':  room == raum}">{{raum}}</button>
    </div>

    <div id="main-box" class="">
      <Calendar v-bind:plan="plan" v-bind:room="room" v-bind:acl="acl"></Calendar>
    </div>

    <Form v-if="acl.rights.write" v-bind:room="room"></Form>


  </div>
</template>

<script>
//console.log('globals',globals);


import Calendar from './components/Calendar.vue'
import Form from './components/Form.vue'
//import Item from './components/Item.vue'



const axios = require('axios').default;

//import Dayjs from 'vue-dayjs';


export default {
  name: 'app',
  components: {
    Calendar,
    Form
  },
  data: function () {
    return {

      loading: false,
      error: false,
      plan: [],

      acl: globals.acl,
      settingsRooms: globals.settingsRooms,
      room: globals.room

    }
  },
  created: function () {



    var that = this;

    EventBus.$on('calendar--changedDate', data => {

      this.showFirstDayWeek = data.von;
      this.showLastDayWeek = data.bis;

      if (data.room) {
        this.room = data.room;
      }
      that.ajaxGet(
        'index.php?page=raumplan&action=getWeek',
        {
          von: this.showFirstDayWeek,
          bis: this.showLastDayWeek,
          room: this.room
        },
        function (response, that) {
          if (response.data && response.data.error != true) {
            that.plan = response.data;
          } else {
            that.plan = [];
          }
          
        }
      );
    }, function () {
      console.log('error');
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


      that.ajaxPost(
        'index.php?page=raumplan&action=saveStunde',
        { data: data.form },
        function (response, that) {
          
          that.error = false;

          if (response.data.error == true && response.data.msg) {
            that.error = response.data.msg;
          } else if (response.data.insert == true) {
            EventBus.$emit('calender--reload', {});
            EventBus.$emit('form--close', {});
          } 
        }
      );

    });

    /*

    EventBus.$on('item--delete', data => {
      
      if (!data.item || !data.item.id) {
        return false;
      }

      console.log(data.item);

      that.ajaxPost(
        'rest.php/SetMensaMeal/delete',
        { data: data.item },
        { },
        function (response, that) {
          
          that.error = false;

          if (response.data.error == true && response.data.msg) {
            that.error = response.data.msg;
          } else if (response.data.done == true) {
            EventBus.$emit('calender--reload', {});
            //EventBus.$emit('form--close', {});
          } 
        }
      );


    });


    EventBus.$on('item--order', data => {
      
      if (!data.item || !data.item.id) {
        return false;
      }

      //console.log(data.item);

      that.ajaxPost(
        'rest.php/SetMensaOrder',
        { data: data.item },
        { },
        function (response, that) {
          
          that.error = false;

          if (response.data.error == true && response.data.msg) {
            that.error = response.data.msg;
          } else if (response.data.done == true) {

            data.item.booked = response.data.booked;
            //EventBus.$emit('calender--reload', {});
            //EventBus.$emit('form--close', {});
          } 
        }
      );


    });
*/


  },
  methods: {

    handlerChangeRoom : function (room) {

      EventBus.$emit('calendar--changedDate', {
        von: this.showFirstDayWeek,
        bis: this.showLastDayWeek,
        room: room
      });

    },
    ajaxGet: function (url, params, callback, error, allways) {
      this.loading = true;
      var that = this;
      axios.get(url, {
        params: params
      })
      .then(function (response) {
        // console.log(response.data);
        if (callback && typeof callback === 'function') {
          callback(response, that);
        }
      })
      .catch(function (resError) {
        //console.log(error);
        if (resError && typeof error === 'function') {
          error(resError);
        }
      })
      .finally(function () {
        // always executed
        if (allways && typeof allways === 'function') {
          allways();
        }
        that.loading = false;
      });  
      
    },
    ajaxPost: function (url, params, callback, error, allways) {

      var that = this;

      var post = new URLSearchParams();
      for (var prop in params.data) {
        post.append(prop, params.data[prop]);
      }

      axios.post(url, post)
          .then(function (response) {
            // console.log(response.data);
            if (callback && typeof callback === 'function') {
              callback(response, that);
            }
          })
          .catch(function (error) {
            //console.log(error);
            if (error && typeof error === 'function') {
              error(error);
            }
          })
          .finally(function () {
            // always executed
            if (allways && typeof allways === 'function') {
              allways();
            }
          });

    }

  }
}
</script>

<style>
</style>
