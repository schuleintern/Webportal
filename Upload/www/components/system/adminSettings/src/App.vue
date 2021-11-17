<template>
  <div>

    <Error v-bind:error="error"></Error>
    <Succeed v-bind:succeed="succeed"></Succeed>

    <div v-if="loading == true" class="overlay">
      <i class="fa fas fa-sync-alt fa-spin"></i>
    </div>

    <h3 class="margin-b-l"><i class="fa fa-sliders-h"></i> Einstellungen</h3>

    <ul class="noListStyle padding-t-l form-style-2">

      <li v-bind:key="index" v-for="(item, index) in settings"
        class="padding-t-m  padding-b-m line-oddEven">

        <Boolean
          v-if="item.typ == 'BOOLEAN'"
          v-bind:item="item"
          v-on:change="triggerToggleValue"></Boolean>
        
        <Number
          v-else-if="item.typ == 'NUMBER'"
          v-bind:item="item"
          v-on:change="triggerToggleValue"></Number>

        <Select
          v-else-if="item.typ == 'SELECT'"
          v-bind:item="item"
          v-on:change="triggerToggleValue"></Select>

        <Html
          v-else-if="item.typ == 'HTML'"
          v-bind:item="item"
          v-on:change="triggerToggleValue"></Html>

        <!--   v-if="item.typ == 'STRING'"-->
        <String
            v-else
            v-bind:item="item"
            v-on:change="triggerToggleValue"></String>

      </li>
    </ul>

  </div>
</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue'
import Succeed from './mixins/Succeed.vue'


import Boolean from './components/boolean.vue'
import Number from './components/number.vue'
import String from './components/string.vue'
import Select from './components/select.vue'
import Html from './components/html.vue'

export default {
  components: {
    Error,
    Succeed,

    Boolean,
    Number,
    String,
    Select,
    Html
  },
  data() {
    return {
      selfURL: globals.selfURL,
      error: false,
      succeed: false,
      loading: false,

      settings: globals.settings
    };
  },
  created: function () {

  },
  methods: {

    triggerToggleValue(obj) {
      //console.log('triggerToggleEvent',obj);

      obj.item.value = obj.value;

      this.saveData(obj);
    },

    saveData: function (obj) {

      
      var that = this;


      if (this.loading == false) {

        this.loading = true;

        this.ajaxPost(
          this.selfURL+'&task=save',
          { settings: this.settings },
          { },
          function (response, that) {
            
            //console.log(response);

            if ( response.data ) {
              that.succeed = 'Einstellungen wurden erfolgreich gespeichert!!';
              that.error = false;
            } else {
              if (response.data.error) {
                that.error = response.data.error;
                that.succeed = false;
              } else {
                that.error = 'Fehler beim Laden. 01';
                that.succeed = false;
              }
              
            }

          },
          function () {
            that.error = 'Fehler beim Laden. 02';
            that.succeed = false;
          },
          function () {
            that.loading = false;
          }
        );

      }

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

};
</script>

<style>

</style>