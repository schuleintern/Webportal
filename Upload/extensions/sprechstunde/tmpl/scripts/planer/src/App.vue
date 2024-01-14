<template>
  <div>

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <Calendar v-bind:week="week" v-bind:slots="slots" v-bind:showDays="showDays" v-bind:acl="acl" v-bind:formData="formData" ></Calendar>


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

      week: [],
      slots: [],

      acl: globals.acl,
      showDays: globals.showDays,
      formData: globals.formData

    };
  },
  created: function () {


    var that = this;

    EventBus.$on('calendar--changedDate', data => {

      this.showFirstDayWeek = data.von;
      this.showLastDayWeek = data.bis;

      this.loadWeek();

    });

  },
  mounted() {

    var that = this;


    EventBus.$on('item--cancel', data => {

      if (!data.item.id || !data.item.createdBy) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('createdBy', data.item.createdBy);

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

            that.loadSlot();
            EventBus.$emit('modal-item--close');

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

      if (!data.form.timeHour
          || !data.form.timeMinute
          || !data.form.title
          || !data.form.day
          || !data.form.duration
          || !data.form.typ
      ) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('timeHour', data.form.timeHour);
      formData.append('timeMinute', data.form.timeMinute);
      formData.append('title', data.form.title);
      formData.append('day', data.form.day);
      formData.append('duration', data.form.duration);
      formData.append('typ', JSON.stringify(data.form.typ) );
      formData.append('id', data.form.id);

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

            that.loadSlot();
            EventBus.$emit('modal-form--close');

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

    loadSlot: function () {

      this.loading = true;
      var that = this;
      axios.get( this.apiURL+'/getSlots')
          .then(function(response){
            if ( response.data ) {
              if (!response.data.error) {
                that.slots = response.data;
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

    loadWeek: function () {

      this.loading = true;
      var that = this;
      const params = new URLSearchParams();
      params.append('von', this.showFirstDayWeek);
      axios.get( this.apiURL+'/getWeek',{
            params: params
      })
      .then(function(response){
        if ( response.data ) {
          if (!response.data.error) {
            that.week = response.data;
            that.loadSlot();
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