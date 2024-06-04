<template>
  <div>


    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <button class="si-btn" v-on:click="handlerOpenForm">New</button>
    <List v-bind:items="items" ></List>
    <ModalForm v-bind:item="form" ></ModalForm>

    <ModalItem v-bind:formfields="formfields" v-bind:fieldtyp="fieldtyp" ></ModalItem>

  </div>
</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'

import ModalForm from './mixins/ModalForm.vue'
import ModalItem from './mixins/ModalItem.vue'

import List from './components/List.vue'


export default {
  components: {
    Error, Spinner, List, ModalForm, ModalItem
  },
  data() {
    return {
      apiURL: globals.apiURL,
      acl: globals.acl,

      loading: false,
      error: false,

      form: {},
      items: globals.items || [],
      fieldtyp: {},
      formfields: false
    };
  },
  created: function () {

    this.loadLists();
    this.loadFieldtyp();


  },
  mounted() {

    var that = this;

    EventBus.$on('item--getFormfields', data => {

      if (!data.id) {
        return false;
      }
      this.loading = true;
      var that = this;
      axios.get( this.apiURL+'/getFormfields/'+data.id)
      .then(function(response){
        if ( response.data ) {
          if (!response.data.error) {

            that.formfields = response.data;

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

    EventBus.$on('item--setFormfields', data => {


      if (!data.form_id) {
        console.log('missing');
        return false;
      }

      console.log(data);

      const formData = new FormData();
      formData.append('form_id', data.form_id);
      formData.append('id', data.form.id || false);
      formData.append('field_id', data.form.field_id);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setFormfield', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.insert) {

            //EventBus.$emit('item--getFormfields');
            EventBus.$emit('item--getFormfields', {
              id: data.form_id
            });

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
      axios.post( this.apiURL+'/setForm', formData, {
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
      axios.get( this.apiURL+'/getForms')
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
    },
    loadFieldtyp: function () {
      var that = this;
      axios.get( this.apiURL+'/getFieldtyps')
      .then(function(response){
        if ( response.data ) {
          if (!response.data.error) {
            that.fieldtyp = response.data;
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
      });
    }

  }

};
</script>

<style>

</style>