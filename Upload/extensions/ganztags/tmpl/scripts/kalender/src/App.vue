<template>
  <div >
    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <ModalAktivity v-bind:activity="activity" v-bind:leaders="leaders"></ModalAktivity>
    <ModalLeader v-bind:leaders="leaders"></ModalLeader>

    <Calendar v-bind:plan="plan" v-bind:acl="acl" v-bind:showDays="showDays"></Calendar>

  </div>
</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'

import Calendar from './components/Calendar.vue'
import ModalAktivity from "./mixins/ModalAktivity.vue";
import ModalLeader from "./mixins/ModalLeader.vue";

export default {
  components: {
    Error, Spinner, Calendar, ModalAktivity, ModalLeader
  },
  data() {
    return {
      apiURL: globals.apiURL,
      acl: globals.acl,
      showDays: globals.showDays,

      loading: false,
      error: false,

      planParams: false,

      items: [],
      plan: [],
      activity: [],
      leaders: []

    };
  },
  created: function () {

    var that = this;

    this.loadAktivity();
    this.loadLeaders();

    EventBus.$on('calendar--changedDate', data => {
      that.planParams = data;
      this.loadPlan(data);
    });



    EventBus.$on('date--group', data => {
      //console.log(data);

      if (!data.group || !data.leader || !data.date) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('group', data.group.id);
      formData.append('title', data.group.title);
      formData.append('duration', data.group.duration);
      formData.append('type', data.group.type);
      formData.append('leader', data.leader.id);
      formData.append('date', data.date);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setDateAktivity', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
          .then(function(response){
            if ( response.data ) {

              if (response.data.error) {
                that.error = ''+response.data.msg;
              } else {
                //data.item.favorite = response.data.favorite;
                //EventBus.$emit('modal-aktivity--close');
                that.loadPlan(that.planParams);
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



    EventBus.$on('date--aktivity', data => {
      console.log(data);

      if (!data.activity || !data.date) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('group', data.activity.id);
      formData.append('title', data.activity.title);
      formData.append('type', data.activity.type);
      formData.append('leader', data.leader.id);
      formData.append('date', data.date);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setDateAktivity', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {

          if (response.data.error) {
            that.error = ''+response.data.msg;
          } else {
            //data.item.favorite = response.data.favorite;
            //EventBus.$emit('modal-aktivity--close');
            that.loadPlan(that.planParams);
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



    EventBus.$on('date--delete', data => {
      console.log(data.item);

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/deleteDate', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {

          if (response.data.error) {
            that.error = ''+response.data.msg;
          } else {
            //data.item.favorite = response.data.favorite;
            //EventBus.$emit('modal-aktivity--close');
            that.loadPlan(that.planParams);
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

    var that = this;


  },
  methods: {

    loadPlan: function (date) {
      if (!date || !date.von || !date.bis) {
        return false;
      }
      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getCalender/'+date.von+'/'+date.bis)
          .then(function (response) {
            if (response.data) {
              if (!response.data.error) {

                that.plan = response.data;

              } else {
                that.error = '' + response.data.msg;
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Laden. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });
    },
    loadLeaders: function () {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getLeaders')
          .then(function (response) {
            if (response.data) {
              if (!response.data.error) {

                that.leaders = response.data;

              } else {
                that.error = '' + response.data.msg;
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Laden. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });
    },

    loadAktivity: function () {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getActivity')
          .then(function (response) {
            if (response.data) {
              if (!response.data.error) {

                that.activity = response.data;

              } else {
                that.error = '' + response.data.msg;
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function () {
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