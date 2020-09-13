<template>
  <div id="app">

    <CalendarForm v-bind:formErrors="formErrors"
      v-bind:kalender="kalender"
      v-bind:calendarSelected="calendarSelected"></CalendarForm>

    <CalendarEintrag v-bind:kalender="kalender"></CalendarEintrag>

    <div v-if="loading == true" class="overlay">
      <i class="fa fa-refresh fa-spin">Loading...</i>
    </div>

    <div id="" class="">
      <CalendarList v-bind:kalender="kalender"></CalendarList>
      <Calendar v-bind:eintraege="eintraege" v-bind:kalender="kalender"></Calendar>
    </div>


  </div>
</template>

<script>
//console.log('globals',globals);

import Calendar from './components/Calendar.vue'
import CalendarList from './components/CalendarList.vue'
import CalendarForm from './components/CalendarForm.vue'
import CalendarEintrag from './components/CalendarEintrag.vue'

const axios = require('axios').default;

axios.defaults.headers.common['schuleinternapirequest'] = '112233' // for all requests

export default {
  name: 'app',
  components: {
    Calendar,
    CalendarList,
    CalendarForm,
    CalendarEintrag
  },
  data: function () {
    return {

      loading: true,
      formErrors: [],

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
        
        if (response.data) {
          
          that.calendarSelected = [ parseInt(response.data.list[0].kalenderID) ];
          EventBus.$emit('list--preselected', {
            selected: that.calendarSelected
          });
          EventBus.$emit('eintrag--load', {});
          that.kalender = response.data.list;
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
          
          //console.log(response.data);

          if (response.data && response.data.list) {
            that.eintraege = response.data.list;
          } else {
            that.eintraege = [];
          }
        }
      );
    });


    EventBus.$on('eintrag--delete', data => {

      if (!data.id) {
        return false;
      }
      that.ajaxPost(
        'rest.php/DeleteKalenderEintrag',
        { data: data.id },
        {},
        function (response, that) {
          
          console.log(response.data);

          if (response.data.done == true) {

            EventBus.$emit('eintrag--load', {});

          } else {
            if (response.data.msg) {
              that.formErrors = [response.data.msg];
            }
          }

        }
      );
    });



    EventBus.$on('eintrag--submit', data => {


      if (data.form.start == ''
        && data.form.title == ''
        && data.form.calenderID == '' ) {
          return false;
      }

      that.ajaxPost(
        'rest.php/SetKalenderEintrag',
        { data: data.form },
        {},
        function (response) {

          if (response.data.done == true) {

            EventBus.$emit('eintrag--form-reset', {});
            EventBus.$emit('eintrag--load', {});

          } else {
            if (response.data.msg) {
              that.formErrors = [response.data.msg];
            }
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
    ajaxPost: function (url, data, params, callback, error, allways) {
      var that = this;
      axios.post(url+'/'+globals.userID, data, {
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
      });  
      
    }


  }
}
</script>

<style>
</style>
