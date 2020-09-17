<template>
  <div id="app">

    <div v-if="error" class="form-modal-error"> 
      <b>Folgende Fehler sind aufgetreten:</b>
      <ul>
        <li>{{ error }}</li>
      </ul>
    </div>

    <h3>Kalender bearbeiten</h3>

    <div class="calendar-list">
      <ul>
        <draggable v-model="kalender" group="people" @start="drag=true" @end="endSort" handle=".handle">
          <li v-bind:key="item.kalenderID" v-for="(item, i) in kalender"
            class="flex-row" v-show="!item.delete">
            <div class="handle"><input type="hidden" v-model="item.kalenderSort" /><i class=" fa fa-sort"></i></div>
            <div><input type="text" v-model="item.kalenderName" /></div>
            <div><input type="text" v-model="item.kalenderColor" placeholder="Farbe" /></div>
            <div>
              <input type="checkbox" v-model="item.kalenderPreSelect" true-value="1" false-value="0" />
              <label>Ausgewählt</label>
            </div>
            <div v-on:click="handlerKalenderRemove(item)"><i class=" fa fa-trash"></i></div>
          </li>
        </draggable>
      </ul>
      <button v-on:click="handlerKalenderAdd">Neuer Kalender hinzufügen</button>
    
      <form ref="form" action="" method="post">
        <input type="hidden" name="data" v-model="kalenderJsonString">
        <input type="hidden" name="action" value="edit">
        <button v-on:click="handlerKalenderSubmit">Speichern</button>
      </form>
    </div>




    <Acl module="apiKalender"></Acl>
   

  </div>
</template>

<script>
//console.log('globals',globals);

const axios = require('axios').default;

//axios.defaults.headers.common['schuleinternapirequest'] = '112233' // for all requests

import draggable from 'vuedraggable'
import Acl from './components/Acl.vue'

export default {
  name: 'app',
  components: {
    draggable,
    Acl
  },
  data: function () {
    return {

      loading: true,
      error: false,

      kalender: [],
      kalenderJsonString: ''
    }
  },
  created: function () {

    //this.acl = globals.acl;

    //console.log(globals);
    var that = this;

    that.ajaxGet(
      'rest.php/GetKalender',
      {},
      function (response, that) {
        if (response.data.error == true && response.data.msg) {
          that.error = response.data.msg;
        } else {
          if (response.data.list) {
            that.kalender = response.data.list;
          } 
        }
      }
    );


  },
  methods: {


    endSort: function () {

      var i = 1;
      this.kalender.forEach(item => {
        item.kalenderSort = i;
        i++;
      });

    },
    handlerKalenderRemove: function (item) {

      item.delete = 1;
      item.kalenderName = 'DELETE';

    },
    handlerKalenderAdd: function () {

      this.kalender.push( {"kalenderID":0 } );
      this.endSort();
    },

    handlerKalenderSubmit: function ($event) {

      this.kalenderJsonString = JSON.stringify(this.kalender);
      this.$refs.form.submit()

    },
    ajaxGet: function (url, params, callback, error, allways) {
      this.loading = true;
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
      this.loading = true;
      var that = this;
      axios.post(url, data, {
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
      
    }


  }
}
</script>

<style>
</style>
