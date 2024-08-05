<template>
  <div>

    <Error v-bind:error="error"></Error>

    <Modal v-bind:data="installModal"></Modal>

    <div v-if="loading == true" class="overlay">
      <i class="fa fas fa-sync-alt fa-spin"></i>
    </div>

    <div class="flex-row">
      <div class="flex-3 page-submenue" >
        <a v-on:click="handlerTab('list')" class="margin-r-xs padding-b-s" :class="{'active' : tab == 'list'}"><i class="fa fas fa-list"></i> Verwalten</a>
        <a v-on:click="handlerTab('install')" class="margin-r-xs padding-b-s " :class="{'active' : tab == 'install'}"><i class="fas fa-plus"></i> Hinzufügen</a>
      </div>
    </div>



    <div v-if="tab == 'list'" class="padding-t-m box">
      <h3 class="padding-l-l"><i class="fa fas fa-plug"></i> Installierte Erweiterungen</h3>
      <table class="si-table si-table-style-firstLeft">
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
        <tr v-bind:key="index" v-for="(item, index) in  extInstalledCollection"
            class="line-oddEven"
            :class="{ 'text-grey' : item.active == 0}">
          <td>{{item.name}} <div class="text-small text-grey">{{item.uniqid}}</div></td>
          <td>{{item.version}}</td>
          <td>
            <button
                v-if="item.active == 1"
                v-on:click="handlerToggleActive(item, $event)"
                class="si-btn si-btn-toggle-on"><i class="fas fa-toggle-on"></i> An</button>
            <button
                v-if="item.active == 0"
                v-on:click="handlerToggleActive(item, $event)"
                class="si-btn si-btn-toggle-off"><i class="fas fa-toggle-off"></i> Aus</button>
          </td>
          <td><span class="text-small">{{item.folder}}</span></td>
          <td><span class="text-small">{{item.json.dependencies}}</span></td>
          <td><button v-show="item.update" class="si-btn" v-on:click="handlerUpdate(item, $event)">Update</button></td>
          <td>
            <button v-show="!item.delete" class="si-btn si-btn-light si-btn-icon" v-on:click="handlerRemoveConfirmed(item)"><i class="fas fa-trash"></i></button>
          </td>

        </tr>
        </tbody>
      </table>
    </div>

    <div v-if="tab == 'install'" class="padding-t-l box">

      <h4 class="padding-l-l"><i class="fas fa-file-upload"></i> Erweiterung hochladen</h4>
      <p class="text-small padding-l-l">(Zip-Archive)</p>
      <p v-show="uploadError" class="text-red padding-t-m padding-b-m">{{uploadError}}</p>
      <div class="flex-row form-style-2 width-40vw padding-l-l">
        <input type="file" accept=".zip" multiple="false" v-on:change="handlerChangeUploadFile" class="flex-1 " />
        <button class="si-btn margin-l-m" v-on:click="handlerUploadInstall"><i class="fas fa-upload"></i> Hochladen & Installieren</button>
      </div>

      <br/>

      <h4 class="padding-l-l"><i class="fas fa-shopping-cart"></i> Aus dem Store</h4>
      <p class="text-small padding-l-l">(URL: {{extensionsServer}})</p>
      <table class="si-table si-table-style-firstLeft">
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
          <td>
            <button  v-if="item.install != true" class="si-btn" v-on:click="handlerInstall(item, $event)"><i class="fa fa-download"></i> Installieren</button>
            <button  v-if="item.install == true" class="si-btn si-btn-off"><i class="fas fa-check"></i> Installiert</button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>


  </div>


</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue';

import Modal from './mixins/Modal.vue';

export default {
  components: {
    Error, Modal
  },
  data() {
    return {
      selfURL: globals.selfURL,
      error: false,
      uploadError: false,
      loading: false,

      uploadFile: false,

      extInstalled: [],
      extStore: globals.extStore,
      extensionsServer: globals.extensionsServer,

      tab: 'list',
      installModal: false
    };
  },
  created: function () {
    this.loadExtensions();

    EventBus.$on('handlerToggleActive', data => {
      if (data.item) {
        this.handlerToggleActive(data.item, false, data.callback);
      }
    });
    EventBus.$on('handlerAddMenue', data => {

      var item = data.item;

      if (!item.uniqid) {
        return false;
      }
      this.loading = true;
      this.error = false;
      var that = this;
      axios.get( this.selfURL+'&task=addMenue&uniqid='+item.uniqid)
      .then(function(response){

        if ( response.data ) {

          if (response.data.error == true) {
            that.error = response.data.msg;
          } else {
            that.error = false;
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
        that.loading = false;
      });

    });


  },
  computed: {
    extInstalledCollection() {

      return this.extInstalled;
    }
  },
  methods: {

    handlerTab: function (tab) {
      this.tab = tab;
    },
    loadExtensions: function () {

      this.loading = true;

      this.extInstalled = [];

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
    handlerToggleActive: function (item, $event, callback) {

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
            if (callback) {
              callback(response.data.active);
            }
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
    handlerRemove: function (item, index) {
      //item.delete = 1;
      //item.active = 99; // bugfix rerender
    },
    handlerRemoveConfirmed: function (item) {

      if (!item.uniqid) {
        return false;
      }

      if ( confirm("Erweiterung vollständig löschen?\nEs werden auch sämtlichen Dateien und Datenbanktabellen gelöscht!") ) {

        var _text = {
          default: 'Entfernen',
          while: '<i class="fa fas fa-sync-alt fa-spin"></i>'
        }

        //$event.srcElement.innerHTML = _text.while;
        this.error = false;

        var that = this;
        axios.get( this.selfURL+'&task=remove&uniqid='+item.uniqid)
        .then(function(response){

          if ( response.data ) {

            //console.log(response.data)

            if (response.data.error == true) {
              that.error = response.data.msg;
              //$event.srcElement.innerHTML = _text.default;
            } else {
              that.error = false;
              that.loadExtensions();
            }

          } else {
            that.error = 'Fehler beim Entfernen. 01';
            //$event.srcElement.innerHTML = _text.default;
          }

        })
        .catch(function(){
          that.error = 'Fehler beim Entfernen. 02';
            //$event.srcElement.innerHTML = _text.default;
        })
        .finally(function () {
          // always executed
        });
      }
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
        done: 'Installiert'
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
            $event.srcElement.classList.add('si-btn-green');
            $event.srcElement.innerHTML = _text.done;
            that.loadExtensions();
            that.installModal = item;
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