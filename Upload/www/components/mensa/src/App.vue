<template>
  <div id="app">
    
    <div v-if="loading == true" class="overlay">
      <i class="fa fa-refresh fa-spin"></i>
    </div>

    <div id="main-box" class="">
      <Calendar  v-bind:dates="dates"></Calendar>
    </div>

  </div>
</template>

<script>
//console.log('globals',globals);

import Calendar from './components/Calendar.vue'


const axios = require('axios').default;

//import Dayjs from 'vue-dayjs';


export default {
  name: 'app',
  components: {
    Calendar
  },
  data: function () {
    return {

      loading: false,

      dates: []

    }
  },
  created: function () {

    
    //console.log( this.$dayjs().format('YYYY-MM-DD HH:mm:ss') );

    //console.log(globals);
    var that = this;


    // EventBus.$on('calendar--changedDate', data => {

    //   this.showFirstDayWeek = data.von;
    //   this.showLastDayWeek = data.bis;

    //   this.loading = true;
    //   that.ajaxGet(
    //     'index.php?page=ausleihe&action=getWeek',
    //     {
    //       von: this.showFirstDayWeek,
    //       bis: this.showLastDayWeek,
    //       filter: this.selectedFilter || false
    //     },
    //     function (response, that) {
    //       that.dates = response.data;
    //       that.loading = false;
    //       EventBus.$emit('form--close', {});
    //     }
    //   );

    // }, function () {
    //   console.log('error');
    //   that.loading = false;
    // });


  },
  methods: {


  }
}
</script>

<style>
</style>
