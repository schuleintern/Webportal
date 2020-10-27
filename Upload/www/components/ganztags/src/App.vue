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
      <Calendar v-bind:dates="dates" v-bind:acl="acl"></Calendar>
    </div>


  </div>
</template>

<script>
//console.log('globals',globals);

import Calendar from './components/Calendar.vue'
// import Form from './components/Form.vue'
// import Item from './components/Item.vue'


const axios = require('axios').default;

//import Dayjs from 'vue-dayjs';


export default {
  name: 'app',
  components: {
    Calendar,
    // Form,
    // Item
  },
  data: function () {
    return {

      loading: false,
      error: false,
      dates: [],
      acl: globals.acl

    }
  },
  created: function () {


    var that = this;

    EventBus.$on('calendar--changedDate', data => {

      this.showFirstDayWeek = data.von;
      this.showLastDayWeek = data.bis;

      that.ajaxGet(
        'index.php?page=mensaSpeiseplan&action=getWeek',
        {
          von: this.showFirstDayWeek,
          bis: this.showLastDayWeek
        },
        function (response, that) {
          if (response.data && response.data.error != true) {
            that.dates = response.data;
          } else {
            that.dates = [];
          }
          
        }
      );
    }, function () {
      console.log('error');
    });

    EventBus.$on('form--submit', data => {
      
      // console.log(data);

      if (!data.form.date || !data.form.title) {
        return false;
      }


      that.ajaxPost(
        'rest.php/SetMensaMeal',
        { data: data.form },
        { },
        function (response, that) {
          
          that.error = false;

          if (response.data.error == true && response.data.msg) {
            that.error = response.data.msg;
          } else if (response.data.done == true) {
            EventBus.$emit('calender--reload', {});
            EventBus.$emit('form--close', {});
          } 
        }
      );

    });



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
