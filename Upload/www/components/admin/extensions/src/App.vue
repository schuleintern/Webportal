<template>
  <div>

    <div v-show="error" class="form-modal-error">
      <b>Folgende Fehler sind aufgetreten:</b>
      <div>{{error}}</div>
    </div>

    <div v-if="loading == true" class="overlay">
      <i class="fa fas fa-sync-alt fa-spin"></i>
    </div>

    <h2>Installierte Erweiterungen</h2>
    <table class="table_1">
      <thead>
        <tr>
          <td>Name</td>
          <td>Version</td>
          <td>Aktiviert</td>
          <td>Ordner</td>
          <td>Abhängigkeit</td>
          <td></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  extInstalled"
          class="line-oddEven"
          :class="{ 'text-grey' : item.active == 0}">
          <td>{{item.name}} <div class="text-small text-grey">{{item.uniqid}}</div></td>
          <td>{{item.version}}</td>
          <td>
            <button
              v-if="item.active == 1"
              v-on:click="handlerToggleActive(item, $event)"
              class="btn text-green"><i class="fas fa-toggle-on"></i></button>
            <button
              v-if="item.active == 0"
              v-on:click="handlerToggleActive(item, $event)"
              class="btn"><i class="fas fa-toggle-off"></i></button>
          </td>
          <td><span class="text-small">{{item.folder}}</span></td>
          <td><span class="text-small">{{item.json.dependencies}}</span></td>
          <td><button v-show="item.update" class="btn btn-blau" v-on:click="handlerUpdate(item, $event)">Update</button></td>
          <td><button class="btn btn-blau" v-on:click="handlerRemove(item, $event)">Entfernen</button></td>

        </tr>
      </tbody>
    </table>

    <br/><br/><hr /><br/>

    <h2>Erweiterungen Hinzufügen</h2>

    <h3>Upload Extension</h3>
    <p class="text-small">(Zip-Archive)</p>
    <p v-show="uploadError" class="text-red padding-t-m padding-b-m">{{uploadError}}</p>
    <div class="flex-row form-style-2">
        <input type="file" accept=".zip" multiple="false" v-on:change="handlerChangeUploadFile" class="flex-1" />
        <button class="btn btn-blau" v-on:click="handlerUploadInstall">Hochladen & Installieren</button>
    </div>

    <br/>

    <h3>Vom Server</h3>
    <p class="text-small">(URL: {{extensionsServer}})</p>
    <table class="table_1">
      <thead>
        <tr>
          <td>Name</td>
          <td>Beschreibung</td>
          <td>Last Release</td>
          <td>Version</td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(item, index) in extStore"
          class="line-oddEven">
          <td>{{item.title}} <div class="text-small text-grey">( {{item.uniqid}} )</div></td>
          <td>{{item.desc}}</td>
          <td>{{item.lastRelease}}</td>
          <td>{{item.version}}</td>
          <td><button class="btn btn-blau" v-on:click="handlerInstall(item, $event)">Installieren</button></td>
        </tr>
        </tbody>
      </table>

  </div>
</template>

<script>

const axios = require('axios').default;

export default {
  data() {
    return {
      selfURL: globals.selfURL,
      error: false,
      uploadError: false,
      loading: false,

      uploadFile: false,

      extInstalled: [],
      extStore: globals.extStore,
      extensionsServer: globals.extensionsServer
    };
  },
  created: function () {
    this.loadExtensions();
  },
  methods: {

    loadExtensions: function () {

      this.loading = true;

      var that = this;
      axios.get( this.selfURL+'&task=api-extensions')
      .then(function(response){
        
        if ( response.data ) {
          that.extInstalled = response.data;
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
    handlerToggleActive: function (item, $event) {

      if (!item.uniqid) {
        return false;
      }

      this.error = false;

      var that = this;
      axios.get( this.selfURL+'&task=toggleActive&uniqid='+item.uniqid)
      .then(function(response){
        
        if ( response.data ) {

          if (response.data.error == true) {
            that.error = response.data.msg;
          } else {
            that.error = false;
            that.loadExtensions();
          }
          
        } else {
          that.error = 'Fehler beim Aktivieren. 01';
        }
        
      })
      .catch(function(){
        that.error = 'Fehler beim Aktivieren. 02';
      })
      .finally(function () {
        // always executed
      }); 

    },
    handlerUpdate: function (item, $event) {

      if (!item.uniqid) {
        return false;
      }

      var _text = {
        default: 'Update',
        while: '<i class="fa fas fa-sync-alt fa-spin"></i>'
      }

      $event.srcElement.innerHTML = _text.while;
      this.error = false;

      var that = this;
      axios.get( this.selfURL+'&task=update&uniqid='+item.uniqid)
      .then(function(response){
        
        if ( response.data ) {

          //console.log(response.data)

          if (response.data.error == true) {
            that.error = response.data.msg;
            $event.srcElement.innerHTML = _text.default;
          } else {
            that.error = false;
            $event.srcElement.innerHTML = _text.default;
            that.loadExtensions();
          }
          
        } else {
          that.error = 'Fehler beim Update. 01';
          $event.srcElement.innerHTML = _text.default;
        }
        
      })
      .catch(function(){
        that.error = 'Fehler beim Update. 02';
          $event.srcElement.innerHTML = _text.default;
      })
      .finally(function () {
        // always executed
      }); 

    },
    handlerRemove: function (item, $event) {

      if (!item.uniqid) {
        return false;
      }

      var _text = {
        default: 'Entfernen',
        while: '<i class="fa fas fa-sync-alt fa-spin"></i>'
      }

      $event.srcElement.innerHTML = _text.while;
      this.error = false;

      var that = this;
      axios.get( this.selfURL+'&task=remove&uniqid='+item.uniqid)
      .then(function(response){
        
        if ( response.data ) {

          //console.log(response.data)

          if (response.data.error == true) {
            that.error = response.data.msg;
            $event.srcElement.innerHTML = _text.default;
          } else {
            that.error = false;
            that.loadExtensions();
          }
          
        } else {
          that.error = 'Fehler beim Entfernen. 01';
          $event.srcElement.innerHTML = _text.default;
        }
        
      })
      .catch(function(){
        that.error = 'Fehler beim Entfernen. 02';
          $event.srcElement.innerHTML = _text.default;
      })
      .finally(function () {
        // always executed
      }); 

    },
    handlerChangeUploadFile: function ($event) {

      this.uploadFile = false;
      this.uploadFile = $event.target.files[0] || $event.dataTransfer.files[0];

    },
    handlerUploadInstall: function () {

      //console.log(this.uploadFile);
      if (!this.uploadFile) {
        return false;
      }

      let formData = new FormData();
      formData.append('file', this.uploadFile);

      //console.log(formData);

      var that = this;
      axios.post( this.selfURL+'&task=uploadInstall',
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }
      ).then(function(response){
        //console.log('SUCCESS!!');

        if ( response.data ) {
          if (response.data.error == true) {
            that.uploadError = response.data.msg;
          } else {
            that.uploadError = false;
            that.loadExtensions();
          }
        } else {
          that.uploadError = 'Fehler beim Installieren. 01';
        }

      })
      .catch(function(){
        //console.log('FAILURE!!');
        that.uploadError = 'Fehler beim Installieren. 02';
      });


    },
    handlerInstall: function (item, $event) {

      if (!item.uniqid) {
        return false;
      }

      var _text = {
        default: 'Installieren',
        while: '<i class="fa fas fa-sync-alt fa-spin"></i>',
        done: 'Installiert...!'
      }
      $event.srcElement.innerHTML = _text.while;
      this.error = false;

      var that = this;
      axios.get( this.selfURL+'&task=install&uniqid='+item.uniqid,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        
        if ( response.data ) {
          if (response.data.error == true) {
            that.error = response.data.msg;
            $event.srcElement.innerHTML = _text.default;
          } else {
            that.error = false;
            $event.srcElement.innerHTML = _text.done;
            that.loadExtensions();
          }
        } else {
          that.error = 'Fehler beim Installieren. 01';
          $event.srcElement.innerHTML = _text.default;
        }
        
      })
      .catch(function(){
        that.error = 'Fehler beim Installieren. 02';
        $event.srcElement.innerHTML = _text.default;
      })
      .finally(function () {
        // always executed
      }); 

    }
  }

};
</script>

<style>
#app {
  font-size: 18px;
  font-family: 'Roboto', sans-serif;
  color: blue;
}
</style>