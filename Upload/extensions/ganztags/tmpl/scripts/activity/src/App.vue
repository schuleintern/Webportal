<template>
  <div >
    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <button v-if="acl.write == 1" class="si-btn" v-on:click="handlerAddItem"><i class="fa fa-plus"></i> Neue Aktivität</button>

    <form v-on:change="handlerChange">
      <table class="si-table si-table-style-allLeft ">
        <thead>
        <tr>
          <th >Name</th>
          <th >Tage</th>
          <th >Raum</th>
          <th >Farbe</th>
          <th  >Info</th>
          <th  >Dauer</th>
        </tr>
        </thead>
        <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  items">
          <td class="">
            <input v-if="acl.write == 1" type="text" v-model="item.title" class="si-input" placeholder="Titel...">
            <span v-else-if="acl.read == 1">{{item.title}}</span>
          </td>
          <td class="">
            <Weekdays v-if="item.days && acl.write == 1" v-bind:days="item.days" @submit="handlerChange"></Weekdays>
            <span v-else-if="acl.read == 1">{{ item.days }}</span>
          </td>
          <td class="">
            <input v-if="acl.write == 1" type="text" v-model="item.room" class="si-input" placeholder="Raum...">
            <span v-else-if="acl.read == 1">{{item.room}}</span>
          </td>
          <td class="">
            <input v-if="acl.write == 1" type="text" v-model="item.color" class="si-input" placeholder="Farbe (hex)...">
            <span v-else-if="acl.read == 1">{{item.color}}</span>
          </td>
          <td class="">
            <input v-if="acl.write == 1" type="text" v-model="item.info" class="si-input" placeholder="kurze Info...">
            <span v-else-if="acl.read == 1">{{item.info}}</span>
          </td>
          <td class="">
            <input v-if="acl.write == 1" type="text" v-model="item.duration" class="si-input" placeholder="Dauer in Minuten...">
            <span v-else-if="acl.read == 1">{{item.duration}}</span>
          </td>
        </tr>
        </tbody>
      </table>
    </form>

  </div>
</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'
import Weekdays from './mixins/Weekdays.vue'




export default {
  components: {
    Error, Spinner, Weekdays
  },
  data() {
    return {
      apiURL: globals.apiURL,
      acl: globals.acl,

      loading: false,
      error: false,

      items: []
    };
  },
  created: function () {

    this.loadLists();

  },
  mounted() {

    var that = this;



  },
  methods: {

    handlerChange: function () {


      if (!this.items || this.items.length < 1) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('items', JSON.stringify(this.items));

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setActivity', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
          .then(function(response){
            if ( response.data ) {

              if (response.data.error) {
                that.error = ''+response.data.msg;
              } else {
                that.items = response.data;
              }
            } else {
              that.error = 'Fehler beim Speichern. 01';
            }
          })
          .catch(function(){
            that.error = 'Fehler beim Speichern. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });


    },
    handlerAddItem: function () {

      this.items.unshift({});
    },

    loadLists: function () {
      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getActivity')
          .then(function (response) {
            if (response.data) {
              if (!response.data.error) {

                that.items = response.data;

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