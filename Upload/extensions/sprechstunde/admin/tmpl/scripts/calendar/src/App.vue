<template>
  <div>

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <Calendar v-bind:plan="plan" v-bind:showDays="showDays" v-bind:acl="acl" v-bind:userSelf="userSelf" ></Calendar>

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
      plan: [],

      acl: globals.acl,
      showDays: globals.showDays,
      userSelf: globals.userSelf

    };
  },
  created: function () {


    var that = this;

    EventBus.$on('calendar--changedDate', data => {

      this.showFirstDayWeek = data.von;
      this.showLastDayWeek = data.bis;

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
          || !data.form.slot_id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('date', data.form.date);
      formData.append('slot_id', data.form.slot_id);
      formData.append('info', data.form.info);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/saveDate', formData, {
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

    loadList: function () {

      this.loading = true;
      var that = this;
      const params = new URLSearchParams();
      params.append('von', this.showFirstDayWeek);
      params.append('admin', true);
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