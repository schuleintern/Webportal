<template>
  <div class="padding-t-m">


    <h3><i class="fa fas fa-user-cog margin-r-m"></i>Moduladministratoren</h3>
    <div class="padding-l-l">
      <ul v-if="users.length" class="noListStyle">
        <li v-bind:key="index" v-for="(item, index) in users"
          class="line-oddEven padding-t-s padding-b-s padding-l-l" >
          {{item.name}}
          <span class="text-small text-grey">({{item.userType}})</span>
        </li>
      </ul>
      <div v-else class="padding-l-l text-grey"><i class="fas fa-ban margin-r-m"></i>Bisher keine</div> 

      <button
        v-show="!modalActive"
        v-on:click="handlerOpenModal"
        class="btn margin-t-m" >
        <i class="fa fa-user-plus margin-r-m"></i>Admins bearbeiten</button>
        
    </div>


    <div class="form-modal" v-on:click.self="handlerCloseModal" v-show="modalActive" >
      <div class="form form-style-2 form-modal-content width-55vw">
        
        <div class="form-modal-close" v-on:click="handlerCloseModal"><i class="fa fa-times"></i></div>

        <div v-if="loading == true" class="overlay">
          <i class="fa fas fa-sync-alt fa-spin"></i>
        </div>

        <div v-show="error" class="form-modal-error">
          <b>Folgende Fehler sind aufgetreten:</b>
          <div>{{error}}</div>
        </div>

        <h3><i class="fa fas fa-user-cog margin-r-m"></i>Moduladministratoren</h3>
        <div class="flex-row">
          <div class="flex-1 margin-r-l padding-t-l">

            <ul class="noListStyle ">
              <li v-bind:key="index" v-for="(item, index) in users"
                class="line-oddEven padding-t-s padding-b-s padding-l-l">
                {{item.name}}
                <span class="text-small text-grey">({{item.userType}})</span>
                <button v-on:click="handlerUserRemove(item)" class="btn text-red margin-l-m"><i class="fa fa-trash"></i></button>
              </li>
            </ul>
            
          </div>
          <div class="flex-1 margin-l-l">

            <h4><i class="fas fa-user-plus margin-r-m"></i>Benutzer Hinzufügen</h4>
            <input type="text"
              v-on:keyup="completeUser"
              v-model="inputUsername"
              class="width-100p"
              placeholder="Benutzer suchen..." />
            <br/>
            <ul class="noListStyle height_35 scrollable-y"
              v-show="userList.length" >
              <li v-bind:key="index" v-for="(item, index) in userList"
                class="line-oddEven padding-t-s padding-b-s padding-l-l clickable"
                v-on:click="handlerUserAdd(item)">
                  {{item.userFirstName}} {{item.userLastName}}
                  <span class="text-small text-grey">{{item.userName}}</span>
              </li>
            </ul>
            
          </div>
        </div>


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
