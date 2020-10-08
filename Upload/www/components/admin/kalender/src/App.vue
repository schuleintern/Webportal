<template>
  <div id="app">

    

    <div class="flex-row">
      <div class="flex-2">

        <div v-if="error" class="form-modal-error"> 
          <b>Folgende Fehler sind aufgetreten:</b>
          <ul>
            <li>{{ error }}</li>
          </ul>
        </div>
        
        <div class="calendar-list">
          <h3>Kalender bearbeiten</h3>
          <ul class="noListStyle">
            <draggable class="" v-model="kalender" group="people" @start="drag=true" @end="endSort" handle=".handle">
              <li v-bind:key="item.kalenderID" v-for="(item, i) in kalender"
                class="flex-row border-radius padding-m  margin-b-m" v-show="!item.delete">

                  <div class="flex">
                    <div class="handle width-2rem flex-1"><input type="hidden" v-model="item.kalenderSort" /><i class=" fa fa-sort"></i></div>
                  </div>

                  <div class="flex-1 flex">
                    <div class="flex">
                      <div class="margin-b-s">
                        <label class="width-7rem">Titel</label>
                        <input type="text" v-model="item.kalenderName" /></div>
                      <div class="margin-b-s">
                        <label class="width-7rem">Farbe</label>
                        <input type="text" v-model="item.kalenderColor" placeholder="#cccccc" /></div>
                      <div>
                        <input type="checkbox" v-model="item.kalenderPreSelect" true-value="1" false-value="0" />
                        <label class="margin-l-m">Vorausgewählt</label>
                      </div>
                      <div>
                        <input type="checkbox" v-model="item.kalenderFerien" true-value="1" false-value="0" />
                        <label class="margin-l-m">Ferien</label>
                        <span v-if="item.kalenderFerien" class="text-red text-small margin-l-m">Vorsicht: Manuell erstellte Kalendereinträge werden durch das System gelöscht.</span>
                      </div>
                    </div>

                    <div class="padding-t-m">
                      <AclModule v-bind:acl="item.kalenderAcl"></AclModule>
                      <span v-if="!item.kalenderAcl" class="text-bold text-red">Zugriffsrechte können erst nach dem Speichern gesetzt werden!</span>
                    </div>
                    <div class="padding-t-m">
                      <button class="btn" v-on:click="handlerKalenderRemove(item)"><i class=" fa fa-trash"></i> Kalender löschen</button>
                    </div>
                  </div>
                  
              </li>
            </draggable>
          </ul>
          <div class="flex-row margin-t-l">
            <button v-on:click="handlerKalenderAdd"
            class="btn btn-grau flex-1 margin-r-m"><i class=" fa fa-plus"></i>Neuer Kalender hinzufügen</button>
        
            <form ref="form" action="" method="post" class="flex-2">
              <input type="hidden" name="data" v-model="kalenderJsonString">
              <input type="hidden" name="action" value="edit">
              <button v-on:click="handlerKalenderSubmit"
                class="btn btn-blau width-100p">Speichern</button>
            </form>
          </div>

        </div>
        
      </div>
      <div class="flex-1">

        <Acl v-bind:moduleName="moduleName"></Acl>

      </div>
    </div>



  </div>
</template>

<script>
//console.log('globals',globals);

const axios = require('axios').default;

//axios.defaults.headers.common['schuleinternapirequest'] = '112233' // for all requests

import draggable from 'vuedraggable'
import Acl from './components/Acl.vue'
import AclModule from './components/AclModule.vue'


export default {
  name: 'app',
  components: {
    draggable,
    Acl,
    AclModule
  },
  data: function () {
    return {

      loading: true,
      error: false,

      kalender: [],
      kalenderJsonString: '',

      moduleName: 'KalenderAllInOne',
      moduleAclID: false,
      moduleAclChildID: false
    }
  },
  created: function () {

    //this.acl = globals.acl;

    //this.moduleAclID = this.moduleName;

    //console.log(globals);
    var that = this;

    that.error = false;
    that.ajaxGet(
      'rest.php/GetKalender',
      {},
      function (response, that) {
        if (response.data.error == true && response.data.msg) {
          that.error = response.data.msg;
        } else {
          if (response.data.list) {
            that.kalender = response.data.list;
            
          } 
        }
      }
    );

    // EventBus.$on('acl--changed', data => {
    //   console.log('acl--changed', data.acl.id, data.moduleName, data.childID );
    //   if (data.childID) {
    //     that.kalender.forEach(item => {
    //       if (item.kalenderID == data.childID) {
    //         console.log('go');
    //         data.acl.moduleClass = '';
    //         data.acl.moduleClassParent = data.moduleName;
    //         item.kalenderAcl = data.acl;
    //       }
    //     });
    //   }
    // });

  },
  methods: {

    handlerOpenAcl: function (item) {

      if (item && item.kalenderID) {
        this.moduleAclChildID = item.kalenderID;
      } else {
        this.moduleAclChildID = false;
      }

      if (item && item.kalenderAclID) {
        this.moduleAclID = item.kalenderAclID;
      } else {
        this.moduleAclID = 0;
      }

    },
    endSort: function () {

      var i = 1;
      this.kalender.forEach(item => {
        item.kalenderSort = i;
        i++;
      });

    },
    handlerKalenderRemove: function (item) {

      item.delete = 1;
      item.kalenderName = 'DELETE';

    },
    handlerKalenderAdd: function () {

      this.kalender.push( {"kalenderID":0 } );
      this.endSort();
    },

    handlerKalenderSubmit: function ($event) {

      this.kalenderJsonString = JSON.stringify(this.kalender);
      this.$refs.form.submit()

    },
    ajaxGet: function (url, params, callback, error, allways) {
      this.loading = true;
      var that = this;
      axios.get(url, {
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

<style>
</style>
