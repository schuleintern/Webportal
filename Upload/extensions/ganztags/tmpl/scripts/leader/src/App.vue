<template>
  <div>
    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>


    <button v-if="acl.write == 1" class="si-btn" v-on:click="handlerAddItem"><i class="fa fa-plus"></i> Neues Teammitglied
    </button>

    <form v-on:change="handlerChange" @submit.prevent="e.preventDefault()">
      <table class="si-table si-table-style-allLeft ">
        <thead>
        <tr>
          <th width="5%">User ID</th>
          <th width="20%">Name</th>
          <th>Tags</th>
          <th>Info</th>
        </tr>
        </thead>
        <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  items">
          <td class="">
            <input v-if="acl.write == 1" type="text" v-model="item.user_id" class="si-input width-12rem">
            <span v-else-if="acl.read == 1">{{ item.user_id }}</span>

            <UserSelect :preselected="[item.user]" @submit="handlerSubmitUser($event, item)"></UserSelect>
          </td>
          <td class="">
            <span v-if="item.user">{{ item.user.name }}</span>
          </td>
          <td class="">
            <Weekdays v-if="item.days && acl.write == 1" v-bind:days="item.days" @submit="handlerChange"></Weekdays>
            <span v-else-if="acl.read == 1">{{ item.days }}</span>
          </td>
          <td class="">
            <input v-if="acl.write == 1" type="text" v-model="item.info" class="si-input">
            <span v-else-if="acl.read == 1">{{ item.info }}</span>
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

import UserSelect from './mixins/UserSelect.vue'


export default {
  components: {
    Error, Spinner, Weekdays, UserSelect
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

    handlerSubmitUser: function (userlist, parent) {
console.log(userlist, parent);
      if (parent && userlist && userlist[0] && userlist[0].id ) {
        parent.user_id = userlist[0].id;
        parent.user = userlist[0];
        this.handlerChange();
      }

    },
    handlerChange: function () {


      if (!this.items || this.items.length < 1) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('items', JSON.stringify(this.items));

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setLeaders', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
          .then(function (response) {
            if (response.data) {

              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.items = response.data;
              }
            } else {
              that.error = 'Fehler beim Speichern. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Speichern. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });


    },
    handlerAddItem: function () {

      this.items.unshift({ "user":{},"user_id":false });
    },

    loadLists: function () {
      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getLeaders')
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