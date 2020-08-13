<template>
  <div id="app" class="flex">

    <div class="box padding-m" v-if="task == ''">
      <h3>Bisherige Backup</h3>
      <div class="padding-m">
        <ul class="flex noListStyle">
          <li>
            <ul class="flex-row noListStyle text-bold">
              <li class="flex-1">Dateiname</b></li>
              <li class="flex-1">Größe</li>
              <li class="flex-1">Änderungsdatum</li>
              <li class="flex-1">Donwload</li>
            </ul>
          </li>
          <li v-bind:key="index" v-for="(entry, index) in  files"
            class="line-oddEven">
            <ul class="flex-row noListStyle text-lineHeight-m">
              <li class="flex-1">{{entry.filename}}</li>
              <li class="flex-1">{{entry.formatSize}}</li>
              <li class="flex-1">{{entry.formatMtime}}</li>
              <li class="flex-1">
                <a v-bind:href="selfURL+'&task=get&path='+encodeURIComponent(entry.filepath)">Download</a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>


    <div class="box padding-m">
      <h3 class="margin-b-m">Backup anlegen</h3>

      <h4 class="text-red" v-if="task != 'end'">Der Vorgang kann mehrere Minuten in Anspruch nehmen. Bitte haben Sie Geduld!</h4>
     
      <div class="padding-m">
        <div v-if="task == 'execute'">
          Vorgang wird durchgeführt...<i class="margin-l-s fa fa-spinner fa-spin"></i>
        </div>
        <div v-if="task == 'end'">
          <h4 v-show="error" class="text-red">{{error}}</h4>
          <a v-bind:href="selfURL">Zurück zur Liste</a>
          <div v-show="result" class="box padding-m" v-html="result"></div>
        </div>
      </div>

      <ul class="flex noListStyle" v-if="task == ''">
        <li class="padding-m flex">
          <label>Auswahl</label>
          <select class="width-form" v-model="art">
            <option value="database">Nur Datenbank</option>
            <option value="data">Eigene Daten und Datenbank</option>
            <option value="system">System und Datenbank</option>
            <option value="full">Eigene Daten, System und Datenbank</option>
          </select>
        </li>
        <li class="padding-m">
          <button class="btn btn-blau" v-on:click="onMakeBackup()">Jetzt Backup erzeugen</button>
        </li>
      </ul>
      
    </div>




  </div>
</template>

<script>

const axios = require('axios').default;

export default {
  name: 'app',
  components: {

  },
  data: function () {
    return {
      selfURL: globals.selfURL,
      files: globals.files,

      task: '',
      error: '',
      result: '',

      art: 'database'
    }
  },
  created: function () {

  
  },
  methods: {

    onMakeBackup: function () {

      if (!this.art) {
        return false;
      }

      this.task = 'execute';

      var that = this;

      axios.get( this.selfURL+'&task=make&action='+this.art,
        {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
      ).then(function(response){
        
        if ( response.data ) {
          that.result = response.data;
          that.task = 'end';
        } else {
          that.error = 'Fehler beim Erzeugen des Backups.';
          that.task = 'end';
        }
        
      })
      .catch(function(){
        that.error = 'Fehler beim Erzeugen des Backups. (Ajax)';
        that.task = 'end';
      })
      .finally(function () {
        // always executed
      }); 

    }
  }
}
</script>

<style>


</style>
