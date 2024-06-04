<template>
  <div class="ext_chat">

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <Groups v-if="route == 'groups'" @loadGroup="handlerLoadGroup" @showGroupForm="handlerShowFormGroup" v-bind:groups="groups"></Groups>

    <FormGroups v-if="route == 'form'" @formSubmitGroup="handlerSubmitFormGroup" @close="handlerFormClose" v-bind:formData="groupForm"></FormGroups>

    <Chat v-if="group && route == 'chat'"
          @close="handlerChatClose"
          @submit="handlerChatSubmit"
          @form="handlerShowFormGroup"
          v-bind:group="group"
          v-bind:form="form"
          v-bind:loading="loading"></Chat>

  </div>
</template>

<script>

const axios = require('axios').default;


import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'

import Groups from './components/Groups.vue'
import FormGroups from './components/FormGroups.vue'
import Chat from './components/Chat.vue'


export default {
  components: {
    Groups, Chat, FormGroups,
    Error, Spinner
  },
  data() {
    return {
      apiURL: globals.apiURL,
      error: false,
      loading: false,

      route: 'groups',

      groups: false, // from AJAX

      group: false, // from User,

      form: {
        msg: ''
      },

      groupForm: false,

      conn: false

    };
  },
  created: function () {

    this.loadGroups();


    this.conn = new WebSocket('wss://rg-intern.de:8080');
    this.conn.onopen = function(e) {
      console.log("Connection established!");
    };

    var that = this;
    this.conn.onmessage = function(e) {
      console.log('get:',e);
      //console.log(that.group.chat);
      let data = JSON.parse(e.data);
      console.log(data);
      that.group.chat.push({
        from: data.from,
        msg: data.msg
      });
    };

console.log(this.conn);

  },
  mounted() {

  },
  methods: {

    handlerSubmitFormGroup: function (obj) {

      if ( !obj.title ) {
        return false;
      }
      if ( !obj.id ) {
        obj.id = 0;
      }
      const formData = new FormData();
      formData.append('title', obj.title);
      formData.append('group_id', obj.id);

      let members = [];
      obj.members.map(function (o,i) {
        members.push( o.id );
      })

      formData.append('members', JSON.stringify(members) );

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setGroup/'+obj.id, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          //that.list = response.data;
          //console.log(response.data.error);
          if (response.data.error == false) {
            //console.log(response.data);
            //that.route = 'groups';
            //that.loadGroups(false);
            //console.log('submit done');
            //that.group = response.data.obj
            //that.handlerLoadGroup(that.group);
            that.loadGroups();

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

    handlerShowFormGroup: function (obj) {
      this.groupForm = obj;
      this.route = 'form';
    },

    handlerLoadGroup: function (item) {

      if (!item.id) {
        return false;
      }
      this.loading = true;
      var that = this;
      axios.get( this.apiURL+'/getGroup/'+item.id)
      .then(function(response){
        if ( response.data ) {
          if (!response.data.error) {
            that.group = response.data;
            that.route = 'chat';



            that.conn.send('ok');

            console.log( {'room': this.group.id, 'task': 'enter'} );
            console.log('---chat' );


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
    loadGroups: function (pageSwitch = true) {

      this.loading = true;
      var that = this;
      axios.get( this.apiURL+'/getGroups')
      .then(function(response){
        if ( response.data ) {
          if (!response.data.error) {
            that.groups = response.data;
            if (pageSwitch) {
              that.route = 'groups';
            }
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
    handlerChatSubmit: function (form) {

      if (!this.group.id || !form.msg) {
        return false;
      }
      //console.log('send:',this.group.id+'#'+form.msg);


      this.conn.send(this.group.id+'#'+form.msg);



      const formData = new FormData();
      formData.append('group_id', this.group.id);
      formData.append('msg', form.msg);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setMsg', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          //that.list = response.data;
          //console.log(response.data.error);
          if (response.data.error == false && response.data.msgObj) {

            if (response.data.msgObj) {
              that.form = {
                msg: ''
              };
              that.group.chat.push(response.data.msgObj);
            }
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
    handlerChatClose: function () {
      this.route = 'groups';
      this.group = false;
    },
    handlerFormClose: function () {
      if (this.group) {
        this.handlerLoadGroup(this.group)
      } else {
        this.route = 'groups';
      }

    }

  }

};
</script>

<style >

.ext_chat {
  margin-top: 3rem;
  margin-left: 20vw;
  background-color: #fff;
  max-width: 40vw;
  border-radius: 3rem;
  box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 15px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px;
  min-height: 70vh;
}
.ext_chat .header {
  height: 5rem;
  box-shadow: rgba(0, 0, 0, 0.25) 0px 10px 70px 0px;
  border-top-left-radius: 3rem;
  border-top-right-radius: 3rem;
  display: flex;
}
.ext_chat .header .title {
  font-size: 160%;
  flex: 1;
  display: flex;
  justify-content: center;
  align-self: center;
}
.ext_chat .footer {
  height: 5rem;
  display: flex;
}
</style>