<template>
  <div id="app">
    
    <div v-if="loading == true" class="overlay">
      <i class="fa fa-refresh fa-spin">Loading...</i>
    </div>

    
    <div class="header flex-row">
      {{eintraege}}
    </div>

    <div id="main-box" class="">
      <CalendarList v-bind:kalender="kalender"></CalendarList>
      <Calendar v-bind:eintraege="eintraege"></Calendar>
    </div>


  </div>
</template>

<script>
//console.log('globals',globals);

import Calendar from './components/Calendar.vue'
import CalendarList from './components/CalendarList.vue'

const axios = require('axios').default;

axios.defaults.headers.common['schuleinternapirequest'] = '112233' // for all requests

export default {
  name: 'app',
  components: {
    Calendar,
    CalendarList
  },
  data: function () {
    return {

      loading: true,

      calendarSelected: [],

      kalender: [],
      eintraege: []

    }
  },
  created: function () {

    //console.log(globals);
    var that = this;

    that.ajaxGet(
      'rest.php/GetKalender',
      {},
      function (response, that) {
        
        console.log(response.data);

        if (response.data) {
          that.kalender = response.data.list;

          that.calendarSelected = [that.kalender[0].kalenderID];
          EventBus.$emit('calendar--eintrag', {});

        }
      }
    );


    EventBus.$on('list--selected', data => {

      that.calendarSelected = data.selected;
      EventBus.$emit('calendar--eintrag', {});
    });


    EventBus.$on('calendar--eintrag', data => {

      that.ajaxGet(
        'rest.php/GetKalenderEintrag/'+that.calendarSelected.join('-'),
        {},
        function (response, that) {
          
          console.log(response.data);

          if (response.data && response.data.list) {
            that.eintraege = response.data.list;
          } else {
            that.eintraege = [];
          }
        }
      );

    });

  },
  methods: {

    ajaxGet: function (url, params, callback, error, allways) {
      this.loading = true;
      var that = this;
      axios.get(url+'/'+globals.userID, {
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
      for (var prop in params) {
        post.append(prop, params[prop]);
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
