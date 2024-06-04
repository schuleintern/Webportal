<template>
  <div>


    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <button class="si-btn" v-on:click="handlerOpenForm">New</button>

    <List v-bind:items="items" ></List>

    <ModalForm v-bind:item="form" ></ModalForm>



  </div>
</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'

import ModalForm from './mixins/ModalForm.vue'

import List from './components/List.vue'


export default {
  components: {
    Error, Spinner, List, ModalForm
  },
  data() {
    return {
      apiURL: globals.apiURL,
      acl: globals.acl,

      loading: false,
      error: false,

      form: {},
      items: globals.items || []
    };
  },
  created: function () {

    this.loadLists();



  },
  mounted() {

    var that = this;

  /*
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
  */

    EventBus.$on('form--submit', data => {

      if (!data.item.title) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('title', data.item.title);
      formData.append('template', data.item.template);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setFieldtyp', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.insert) {

            that.loadLists();
            EventBus.$emit('modal-form--close', {});

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

    handlerOpenForm: function () {
      EventBus.$emit('modal-form--open');
    },
    loadLists: function () {
      this.loading = true;
      var that = this;
      axios.get( this.apiURL+'/getFieldtyps')
      .then(function(response){
        if ( response.data ) {
          if (!response.data.error) {

            that.items = response.data;

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