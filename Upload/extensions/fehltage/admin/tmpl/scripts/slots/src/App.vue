<template>

  <div class="box">


    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <div  class="flex-row padding-t-s padding-l-s">
      <button v-on:click="handlerAddSlot" class="si-btn si-btn-green margin-r-m"><i class="fa fa-plus"></i> Add</button>
      <button v-if="edit" v-on:click="handlerSubmitSlot" class="si-btn"><i class="fa fa-save"></i> Save</button>
    </div>

    <div class="si-form">
      <ul>
        <li v-bind:key="index" v-for="(item, index) in  slots" class="">


          <div v-if="item.edit" class="flex-row">
            <div class="flex-1 flex margin-r-m">
              <label>Anzahl Tag</label>
              <input type="text" v-model="item.tage"/>
            </div>
            <div class="flex-1 flex">
              <label>Info</label>
              <textarea v-model="item.info"></textarea>
            </div>
          </div>
          <div v-else class="flex-row">
            <div class="flex-1">
              <h2>{{ item.tage }} Tage</h2>
            </div>
            <div class="text-small flex-2 padding-t-m">{{ item.info }}</div>
            <div class="">
              <button v-on:click="handlerEditSlot(item)" class="si-btn"><i class="fa fa-edit"></i> Edit</button>
            </div>

          </div>
        </li>
      </ul>
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
      apiURL: window.globals.apiURL,
      acl: window.globals.acl,
      error: false,
      loading: false,
      page: 'list',

      item: false,
      slots: window.globals.slots,
      edit: false


    };
  },
  computed: {},
  created: function () {


  },
  methods: {

    handlerEditSlot(item) {

      if (!item) {
        return false;
      }
      item.edit = true;
      this.edit = true;

    },
    handlerAddSlot() {

      this.slots.push({'edit': true, 'tage': 0, 'info': ''});
      this.edit = true;

    },
    handlerSubmitSlot() {

      if (!this.slots) {
        return false;
      }

      this.loading = true;
      var that = this;
      const formData = new FormData();
      formData.append('slots', JSON.stringify(this.slots));

      axios.post(this.apiURL + '/setAdminSlots', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            that.slots = response.data.data;
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      }).catch(function () {
        that.error = 'Fehler beim Laden. 02';
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
