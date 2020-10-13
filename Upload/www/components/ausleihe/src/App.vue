<template>
  <div id="app">
    
    <div v-if="loading == true" class="overlay">
      <i class="fa fa-refresh fa-spin"></i>
    </div>

    
    <div class="header">
      <div class="spacer"></div>
      <div class="filter dropdown">
        <a href="#" class="dropdown-toggle btn btn-info" data-toggle="dropdown">{{selectedFilterName}} <span class="caret"></span></a>
        <ul class="dropdown-menu" role="menu">
          <li @click="setFilterHandler(false, $event)">Alle</li>
          <li class="divider"></li>
          <li v-bind:key="index" v-for="(item, index) in objectsEasy"
            @click="setFilterHandler(item, $event)">
            {{item.objektName}}
          </li>
        </ul>
      </div>
    </div>

    <div id="main-box" class="">
      <Calendar  v-bind:dates="dates"></Calendar>
      <Form
        v-bind:errorMsg="errorMsg"
        v-bind:disableObjects="disableObjects"
        v-bind:dates="dates"></Form>
    </div>

    <div v-show="myDates.length > 0" class="nextEventsWrap box box-success">
      <NextEvents v-bind:dates="myDates"></NextEvents>
    </div>

  </div>
</template>

<script>
//console.log('globals',globals);

import Calendar from './components/Calendar.vue'
import Form from './components/Form.vue'
import NextEvents from './components/NextEvents.vue'

const axios = require('axios').default;

//axios.defaults.headers.common['x-authorization'] = '112233' // for all requests

export default {
  name: 'app',
  components: {
    Calendar,
    Form,
    NextEvents
  },
  data: function () {
    return {

      dates: [],
      errorMsg: '',
      loading: false,
      myDates: [],
      disableObjects: [],
      objectsEasy: globals.objectsEasy,

      showFirstDayWeek: false,
      showLastDayWeek: false,
      selectedFilterName: 'Filter ',
      selectedFilter: false
    }
  },
  created: function () {

    //console.log(globals);
    var that = this;

    EventBus.$on('nextevents--reload', data => {

      that.ajaxGet(
        'index.php?page=ausleihe&action=myDates',
        {},
        function (response, that) {
          
          if (response.data) {
            that.myDates = response.data;
          }
        }
      );

    });

    EventBus.$on('nextevents--delete', data => {

      this.loading = true;
      var ausleiheID = data.ausleiheID;
      if (!ausleiheID) {
        return false;
      }
      that.ajaxGet(
        'index.php?page=ausleihe&action=deleteEvent',
        {
          ausleiheID: ausleiheID
        },
        function (response, that) {

          if (response.data.delete == true) {
           
            EventBus.$emit('calendar--reload', {} );
            EventBus.$emit('nextevents--reload', {});

          } else {
            that.errorMsg = response.data.errorMsg;
          }

          that.loading = false;
        }
      );

    });

    EventBus.$on('form--check', data => {

      //console.log('submit',data);

      that.disableObjects = [];
      that.ajaxPost(
        'index.php?page=ausleihe&action=checkEvent',
        data,
        function (response, that) {
          
          if (response.data.check == true) {
            //console.log(response.data.objects);
            that.disableObjects = response.data.objects;
          } else {
            that.errorMsg = response.data.errorMsg;
          }
        }
      );

    });

    EventBus.$on('form--submit', data => {

      //console.log('submit',data);

      this.loading = true;
      that.ajaxPost(
        'index.php?page=ausleihe&action=setEvent',
        data,
        function (response, that) {

          if (response.data.insert == true) {
            EventBus.$emit('calendar--reload', {} );
            EventBus.$emit('form--close', {});
            EventBus.$emit('nextevents--reload', {});

          } else {
            that.errorMsg = response.data.errorMsg;
          }
          that.loading = false;
        }
      );

    });


    EventBus.$on('calendar--changedDate', data => {

      this.showFirstDayWeek = data.von;
      this.showLastDayWeek = data.bis;

      this.loading = true;
      that.ajaxGet(
        'index.php?page=ausleihe&action=getWeek',
        {
          von: this.showFirstDayWeek,
          bis: this.showLastDayWeek,
          filter: this.selectedFilter || false
        },
        function (response, that) {
          that.dates = response.data;
          that.loading = false;
          EventBus.$emit('form--close', {});
        }
      );

    }, function () {
      console.log('error');
      that.loading = false;
    });

    EventBus.$on('calendar--addDate', data => {

      EventBus.$emit('form--open', {
        datum: data.day,
        stunde: data.hour
      });
      return false;

    });

  },
  methods: {

    setFilterHandler: function (object, event) {

  
      if (object.objektID) {
        this.selectedFilter = {'object': object };
      } else {
        this.selectedFilter = false;
      }
      //console.log(object);
      this.selectedFilterName = object.objektName || 'Filter';
      
      EventBus.$emit('calendar--changedDate', {
        von: this.showFirstDayWeek,
        bis: this.showLastDayWeek
      });

    },
    ajaxGet: function (url, params, callback, error, allways) {

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
