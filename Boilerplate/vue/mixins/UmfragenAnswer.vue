<template>
  <div class="">
    <ul v-if="openForm">
      <li v-bind:key="index" v-for="(obj, index) in  item.childs"  class="flex" :class="{'required': !obj.value}">
        <h4>{{ index+1 }}. {{ obj.title }}</h4>

        <div v-if="obj.typ == 'text'" class="flex">
          <input type="text" v-model="obj.value" >
        </div>

        <div v-if="obj.typ == 'number'" class="flex">
          <input type="number" v-model="obj.value">
        </div>

        <div v-if="obj.typ == 'boolean'" class="flex-row flex-center-center">
          <button class="si-btn si-btn-toggle-off margin-r-m" v-on:click="handlerBoolean(1, obj)" :class="{'si-btn-active': obj.value == 1}">
            <i class="fa fas fa-toggle-on"></i> Ja</button>
          <button class="si-btn si-btn-toggle-off" v-on:click="handlerBoolean(2,obj)"  :class="{'si-btn-active': obj.value == 2}">
            <i class="fa fas fa-toggle-off"></i> Nein</button>
        </div>

      </li>
      <li v-if="btnSave">
        <button class="si-btn" @click="handlerSave"><i class="fa fa-save"></i> Speichern</button>
      </li>
    </ul>
    <div v-else>
      <h4 class="text-green padding-r-l"><i class="fa fa-poll"></i> Erfolgreich Gepeichert</h4>
    </div>
  </div>
</template>

<script>

const axios = require('axios').default;


export default {
  components: {

  },
  data() {
    return {
      item: {},
      openForm: true
    };
  },
  props: {
    form: Object,
    btnSave: Boolean
  },
  created: function () {
    this.item = this.form;
  },
  methods: {


    handlerSave() {

      this.loading = true;
      var that = this;
      const formData = new FormData();
      formData.append('childs', JSON.stringify(this.item.childs) );

      let sessionID = localStorage.getItem('session');
      if (sessionID) {
        sessionID = sessionID.replace('__q_strn|','');
      }

      axios.post('rest.php/umfragen/setAnswer', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
          'auth-app': window.globals.apiKey,
          'auth-session': sessionID
        }
      }).then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            //console.log(response.data)
            that.openForm = false;
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      }).catch(function () {
        that.error = 'Fehler beim Laden. 02';
      }).finally(function () {
        that.loading = false;
      });


    },
    handlerBoolean(val,item) {
      item.value = val;
    },

  }
};
</script>