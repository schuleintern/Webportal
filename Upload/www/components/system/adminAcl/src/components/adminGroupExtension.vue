<template>
  <div class="">


    <h3><i class="fa fas fa-user-cog"></i>Moduladministratoren</h3>

    <ul v-if="users.length">
      <li v-bind:key="index" v-for="(item, index) in users">
        {{item.name}} [{{item.userType}}]
      </li>
    </ul>
    <div v-else ><i class="fas fa-ban"></i>Bisher keine</div> 



    <button
      v-show="!modalActive"
      v-on:click="handlerOpenModal" >
      <i class="fa fa-user-plus"></i>Admins bearbeiten</button>

    <div class="form-modal" v-on:click.self="handlerCloseModal" v-show="modalActive" >
      <div class="form form-style-2 form-modal-content width-40vw">
        
        <div class="form-modal-close" v-on:click="handlerCloseModal"><i class="fa fa-times"></i></div>
        
        <br />

        <div v-if="loading == true" class="overlay">
          <i class="fa fas fa-sync-alt fa-spin"></i>
        </div>

        <div v-show="error" class="form-modal-error">
          <b>Folgende Fehler sind aufgetreten:</b>
          <div>{{error}}</div>
        </div>

        <h2>Moduladministratoren</h2>
        <ul>
          <li v-bind:key="index" v-for="(item, index) in users">
            {{item.name}} [{{item.userType}}]
            <button v-on:click="handlerUserRemove(item)"><i class="fa fa-trash"></i></button>
          </li>
        </ul>

        <h3>Benutzer Hinzufügen</h3>
          
        <input type="text" v-on:keyup="completeUser" v-model="inputUsername" placeholder="Benutzer..." />
        
        <ul>
          <li v-bind:key="index" v-for="(item, index) in userList">
            <button v-on:click="handlerUserAdd(item)">
              {{item.userFirstName}} {{item.userLastName}} [{{item.userName}}]
            </button>
          </li>
        </ul>



      </div>
    </div>
  </div>

</template>


<script>

const axios = require('axios').default;

export default {
  name: 'adminGroupExtension',
  props: {
    users: Array
  },
  data(){
    return {
      loading: false,
      error: false,
      selfURL: globals.selfURL,
      modalActive: false,

      //users: [],

      inputUsername: '',
      userList: false
    }
  },
  created: function () {

  },
  computed: {

  },
  methods: {

    handlerUserRemove: function (user) {

      if (!this.selfURL || !user || !user.userID) {
        return false;
      }
      if (this.loading != false) {
        return false;
      }
      this.loading = true;

      var that = this;
      that.ajaxPost(
        this.selfURL+'&task=removeAdmin',
        { userID: user.userID },
        {},
        function (response, that) {
          
          if (response.data && response.data.users) {
            that.error = false;
            that.users = response.data.users;
            //that.modalActive = false;
          } else {
            that.error = "Fehler beim Entfernen. 01"
            that.succeed = false;
          }
        },
        function () {
          that.error = 'Fehler beim Entfernen. 02';
          that.succeed = false;
        },
        function () {
          that.loading = false;
        }
      );


    },
    handlerUserAdd: function (user) {

      if (!this.selfURL || !user || !user.userID) {
        return false;
      }
      if (this.loading != false) {
        return false;
      }
      this.loading = true;

      var that = this;
      that.ajaxPost(
        this.selfURL+'&task=addAdmin',
        { userID: user.userID },
        {},
        function (response, that) {
          
          if (response.data && response.data.users) {
            that.error = false;
            that.users = response.data.users;
            //that.modalActive = false;
          } else {
            that.error = "Fehler beim Hinzufügen. 01"
            that.succeed = false;
          }
        },
        function () {
          that.error = 'Fehler beim Hinzufügen. 02';
          that.succeed = false;
        },
        function () {
          that.loading = false;
        }
      );


    },
    completeUser: function () {

      if (!this.selfURL) {
        return false;
      }
      if (this.loading != false) {
        return false;
      }
      this.loading = true;

      var that = this;
      that.ajaxPost(
        this.selfURL+'&task=completeUserName',
        { input: this.inputUsername },
        {},
        function (response, that) {
          
          if (response.data && response.data.users) {
            that.error = false;
            that.userList = response.data.users
          } else {
            that.error = "Fehler beim Laden. 01"
            that.succeed = false;
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


    },

    handlerCloseModal: function () {
      this.modalActive = false;
    },
    handlerOpenModal: function () {
      this.modalActive = true;
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
