<template>
  <div>

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <Calendar v-bind:dates="dates" v-bind:acl="acl" ></Calendar>


  </div>
</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'

import Calendar from './components/Calendar.vue'

export default {
  components: {
    Error, Spinner, Calendar
  },
  data() {
    return {
      apiURL: globals.apiURL,

      loading: false,
      error: false,

      dates: [],

      acl: globals.acl

    };
  },
  created: function () {

    this.loadDates();

    EventBus.$on('date--delete', data => {

      if (!data.date.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.date.id);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/deleteDate', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error == false) {

            that.loadDates();

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
  mounted() {

  },
  methods: {

    loadDates: function () {

      this.loading = true;
      var that = this;
      axios.get( this.apiURL+'/getDates',{
            params: []
      })
      .then(function(response){
        if ( response.data ) {
          if (!response.data.error) {
            that.dates = response.data;
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