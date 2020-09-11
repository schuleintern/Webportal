<template>
  <div id="app">
    {{form}}

    <div class="form">
      <form>
        Day:
        <input type="text" v-model="form.day" />
        Start Clock:
        <input type="text" v-model="form.start" />
        <vue-timepicker v-model="form.start" format="HH:mm" :minute-interval="5"></vue-timepicker>
        End Clock:
        <input type="text" v-model="form.end"  />
        <vue-timepicker v-model="form.end" format="HH:mm" :minute-interval="5"></vue-timepicker>
        Title:
        <input type="text" v-model="form.title" />
        Place:
        <input type="text" v-model="form.place" />
        Title:
        <textarea v-model="form.comment">
        </textarea>

      </form>
    </div>

    <div v-if="loading == true" class="overlay">
      <i class="fa fa-refresh fa-spin">Loading...</i>
    </div>

    
    <div class="header flex-row">
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

import VueTimepicker from 'vue2-timepicker'


const axios = require('axios').default;

axios.defaults.headers.common['schuleinternapirequest'] = '112233' // for all requests

export default {
  name: 'app',
  components: {
    Calendar,
    CalendarList,
    VueTimepicker
  },
  data: function () {
    return {

      loading: true,

      calendarSelected: [],

      kalender: [],
      eintraege: [],

      form: {
        day: false,
        start: ''
      }

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
          EventBus.$emit('eintrag--load', {});

        }
      }
    );


    EventBus.$on('list--selected', data => {

      that.calendarSelected = data.selected;
      EventBus.$emit('eintrag--load', {});
    });


    EventBus.$on('eintrag--load', data => {

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

    EventBus.$on('eintrag--add', data => {

      if (!data.day) {
        return false;
      }

      this.form.day = data.day;

    });

  },
  methods: {

    handlerChangeFormStart: function () {

      if (this.form.end == '') {
        this.form.end = this.form.start;
      }
    },


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
