<template>

  <div>

    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>
    <AjaxError v-bind:error="error"></AjaxError>

    <Modal v-bind:data="selectedItem"></Modal>

    <ul class="page-submenue">
      <li v-on:click="handlerOpenFolder('posteingang')" class="item">Posteingang</li>
      <li v-on:click="handlerOpenFolder('gesendet')" class="item">Gesendete</li>
      <li v-on:click="handlerOpenFolder('papierkorb')" class="item">Papierkorb</li>
      <li v-on:click="handlerOpenFolder('archiv')" class="item">Archiv</li>
      <li v-bind:key="index" v-for="(item, index) in  folders" v-on:click="handlerOpenFolder('anderer')" class="item">
        <span>{{ item.folderName }}</span>
      </li>
    </ul>

    <div class="box">
      <div class="box-body">

        <div class="si-table-fixedHeader">
          <table class="si-table si-table-style-allLeft ">
            <thead>
            <tr>
              <th></th>
              <th></th>
              <th>Absender</th>
              <th>Betreff</th>
              <th>Anhänge</th>
              <th>Empfangen</th>
            </tr>
            </thead>
            <tbody>
            <tr v-bind:key="index" v-for="(item, index) in  messages" v-on:click="handlerOpenMessage(item)">
              <td>
                <i v-if="item.isRead == 1" class="fa fa-envelope-open"></i>
                <i v-else class="fa fa-envelope"></i>
              </td>
              <td>
                <i v-if="item.priority == 'HIGH'" class="fa fa-arrow-up text-red"></i>
                <i v-if="item.priority == 'LOW'" class="fa fa-arrow-down text-green"></i>
              </td>
              <td>
                <User v-if="item.from" size="line" v-bind:data="item.from"></User>
              </td>
              <td>{{ item.subject }}</td>
              <td><i v-if="item.attachments" class="fa fa-file" title="Anhänge"></i></td>
              <td>{{ item.time }}</td>
            </tr>
            </tbody>
          </table>
        </div>

        <div v-if="message">
          <ul>
            <li>
              <label>Von</label>
              {{ message.text }}
            </li>
            <li class="">
              <label>Betreff</label>
              {{ message.subject }} - {{ message.time }}
            </li>
            <li>
              <label>Empfänger</label>
              {{ message.recipients }}
            </li>
            <li>
              <label>Kopieempfänger</label>
              {{ message.text }}
            </li>


          </ul>
          <div>
            {{ message.text }}
          </div>
          <div>
            {{ message.groupID }}
          </div>
        </div>
      </div>
    </div>


  </div>
</template>

<script>

const axios = require('axios').default;

import User from './mixins/User.vue'
import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'
import Modal from './mixins/Modal.vue'

export default {
  components: {
    User, AjaxError, AjaxSpinner, Modal
  },
  data() {
    return {
      apiURL: globals.apiURL,
      error: false,
      loading: false,

      folders: false, // from AJAX
      messages: false, // from AJAX
      message: false // from AJAX


    };
  },
  created: function () {

    this.loadFolders();

  },
  mounted() {
    /*
        EventBus.$on('item-submit', data => {
          //console.log(data)

          const formData = new FormData();
          formData.append('id', data.id);
          formData.append('einheiten', data.einheiten);

          this.loading = true;
          var that = this;
          axios.post( this.apiURL+'/orderSlot', formData, {
              headers: {
                'Content-Type': 'multipart/form-data'
              }
            })
            .then(function(response){
              if ( response.data ) {
                //that.list = response.data;
                //console.log(response.data.error);
                if (response.data.error == false) {
                  that.loadList();
                  that.selectedItem = false;
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
  },
  methods: {

    handlerOpenMessage: function (message) {

      console.log(message)
      if (!message.id) {
        return false
      }

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getMessage/' + message.id)
          .then(function (response) {
            if (response.data) {
              if (!response.data.error) {
                that.message = response.data;
                //console.log(response.data);
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
    handlerOpenFolder: function (folder) {

      if (!folder) {
        return false
      }

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getMessages/' + folder)
          .then(function (response) {
            if (response.data) {
              if (!response.data.error) {
                that.messages = response.data;
                //console.log(response.data);
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
    loadFolders: function () {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getMyFolders')
          .then(function (response) {
            if (response.data) {
              if (!response.data.error) {
                that.folders = response.data;
                //console.log(response.data);
                that.loading = false;
                that.handlerOpenFolder('posteingang');
              } else {
                that.error = '' + response.data.msg;
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function () {
            that.loading = false;
            that.error = 'Fehler beim Laden. 02';
          })
          .finally(function () {
            // always executed
            //
          });

    }


  }

};
</script>

<style>

</style>