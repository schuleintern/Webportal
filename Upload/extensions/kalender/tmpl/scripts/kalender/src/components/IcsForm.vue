<template>

  <AjaxError :error="error"></AjaxError>
  <AjaxSpinner :loading="loading"></AjaxSpinner>

  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box">
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content  ">


        <div class="si-details">
          <ul>
            <li>
              <label>Privater ICS Feed aktivieren</label>
              <FormToggle :input="form.icsState" @change="handlerICS"></FormToggle>
            </li>
            <li v-if="form.icsState">
              <label>Dein privater ICS Feed</label>
              <div class="flex-row">
                <input class="si-input flex-1" v-model="form.icsPrivateURL">
                <button class="si-btn margin-l-m"><i class="fas fa-download"></i> Download</button>
              </div>
            </li>
            <li>
              <label>Öffentlicher ICS Feed</label>
              <div class="flex-row">
                <input class="si-input flex-1" v-model="form.icsPublicURL">
                <button class="si-btn margin-l-m"><i class="fas fa-download"></i> Download</button>
              </div>
            </li>
          </ul>
        </div>

      </div>

    </div>
  </div>


</template>

<script>
import AjaxError from '../mixins/AjaxError.vue'
import AjaxSpinner from '../mixins/AjaxSpinner.vue'
const axios = require('axios').default;
//import {onMounted, ref} from "vue";
import FormToggle from "../mixins/FormToggle";

export default {
  name: 'IcsForm',
  components: {
    AjaxError, AjaxSpinner,
    FormToggle
  },
  setup() {
    return {}
  },
  data() {
    return {
      error: false,
      loading: false,
      apiURL: window.globals.apiURL,
      open: false,
      form: {}
    };
  },
  props: {
    calendars: Array
  },
  created: function () {

    var that = this;
    this.$bus.$on('ics-form--open', data => {
      /*if (data.form) {
        that.form = data.form;
      }*/
      that.loadICS(() => {
        that.open = true;
      });


    });
    this.$bus.$on('event-form--close', data => {
      that.open = false;
      that.form = [];
    });


  },
  methods: {

    activeICS(icsState) {

      const formData = new FormData();
      formData.append('icsState', icsState);

      this.loading = true;
      var that = this;
      this.axios.post(this.apiURL + '/setICS', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                //console.log('DONE!!!!!!!!')

                that.loadICS();
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
    loadICS(callback) {

      this.loading = true;
      var that = this;
      this.axios.get(this.apiURL + '/getICS')
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.form = response.data;
                if (callback) {
                  callback();
                }
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
    handlerICS: function (data) {
      this.form.icsState = data.value;
      this.activeICS(this.form.icsState);
    },
    handlerClose: function () {
      this.$bus.$emit('event-form--close');
    }
  }


};
</script>

<style>
</style>