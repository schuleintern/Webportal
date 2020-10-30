<template>
  <div id="app">

    <div v-show="error" class="form-modal-error">
      <b>Folgende Fehler sind aufgetreten:</b>
      <div>{{error}}</div>
    </div>

    <div v-if="loading == true" class="overlay">
      <i class="fa fas fa-sync-alt fa-spin"></i>
    </div>

    <div id="main-box" class="">
      <Calendar v-bind:list="list" v-bind:acl="acl"></Calendar>
    </div>

    <Item v-bind:acl="acl"></Item>

  </div>
</template>

<script>
//console.log('globals',globals);

import Calendar from './components/Calendar.vue'
// import Form from './components/Form.vue'
import Item from './components/Item.vue'


const axios = require('axios').default;

//import Dayjs from 'vue-dayjs';


export default {
  name: 'app',
  components: {
    Calendar,
    // Form,
    Item
  },
  data: function () {
    return {

      loading: false,
      error: false,
      list: [],
      acl: globals.acl

    }
  },
  created: function () {


    var that = this;

    EventBus.$on('calendar--changedDate', data => {

      that.ajaxGet(
        'index.php?page=ganztagsCalendar&action=getWeek',
        {
          days: JSON.stringify(data.days)
        },
        function (response, that) {
          if (response.data && response.data.error != true) {
            that.list = response.data;
          } else {
            that.list = [];
          }
          
        }
      );
    }, function () {
      console.log('error');
    });



  },
  methods: {

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
    ajaxPost: function (url, data, params, callback, error, allways) {
      this.loading = true;
      var that = this;
      axios.post(url, data, {
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
      
    }

  }
}
</script>

<style>
</style>
