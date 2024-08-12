<template>


  <div>

    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <div class="flex-row">
      <h4 class="flex-1"><i class="fas fa fa-table"></i> <span v-if="list && list.title">{{list.title}}</span><span v-else>Stundenplan</span></h4>
      <div>
        <a class="si-btn si-btn-border si-btn-icon si-btn-small" href="index.php?page=ext_stundenplan"><i
            class="fas fa-external-link-alt"></i></a>
      </div>
    </div>
    <ul v-if="getPlan()" class="noListStyle">
      <li v-bind:key="index" v-for="(item, index) in  getPlan()" class="line-oddEven padding-l-m padding-r-m flex-row">

        <div class="text-big-m text-grey  padding-r-m margin-r-s flex flex-center-center">{{ index + 1 }}.</div>
        <div class="">
          <div v-bind:key="i" v-for="(s, i) in  item" class="si-box margin-r-m">
            <span v-if="s.grade" class="margin-r-m"><i class="fa fa-users text-grey margin-r-s"></i> {{ s.grade }}</span>
            <span v-if="s.subject" class="margin-r-m"><i class="fa fa-flask text-grey margin-r-s"></i> {{ s.subject }}</span>
            <span v-if="s.teacher	" class="margin-r-m"><i class="fa fa-user text-grey margin-r-s"></i> {{ s.teacher	 }}</span>
            <span v-if="s.room" class="margin-r-m"><i class="fa fa-door-open text-grey margin-r-s"></i> {{ s.room }}</span>
          </div>
        </div>


      </li>
    </ul>
    <div v-else>
      <div class="padding-m"><i>- Keine Inhalte vorhanden -</i></div>
    </div>


  </div>
</template>

<script>
import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

const axios = require('axios').default;

export default {

  name: 'App',
  components: {
    AjaxError, AjaxSpinner
  },
  data() {
    return {

      apiURL: 'rest.php/stundenplan',
      apiKey: window._widget_stundenplan_apiKey,
      dayNumber: window._widget_stundenplan_day,
      list: []

    };
  },
  created() {
    this.loadItems();
    //this.list = window._widget_kalender_events.today;
    //console.log(this.list)
  },
  methods: {

    getPlan() {
      if (this.list && this.list.plan && this.dayNumber) {
        return this.list.plan[this.dayNumber];
      }
      return false;
    },
    loadItems(item) {

      const formData = new FormData();

      if (item) {

        //console.log(item);

        formData.append('key', item[1]);
        if (item[1] == 'teacher' && item[2]) {
          formData.append('value', item[2]);
        } else {
          formData.append('value', item[0]);
        }
      }

      let sessionID = localStorage.getItem('session')
      if (sessionID) {
        sessionID = sessionID.replace('__q_strn|', '');
      }


      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/getStundenplan', formData, {
        headers: {
          'auth-app': this.apiKey,
          'auth-session': sessionID
        }
      }).then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            that.list = response.data;
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
        that.loading = false;
      }).catch(function () {
        that.error = 'Fehler beim Laden. 02';
        that.loading = false;
      }).finally(function () {
        // always executed
        that.loading = false;
      });
    },

  }
}
</script>

<style>

</style>
