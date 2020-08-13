<template>
  <div id="app" class="flex">

    <button v-on:click="onMakeBackup()">GO</button>
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
            <a v-bind:href="selfURL+'&task=get&path='+entry.filepath">Download</a>
          </li>
        </ul>
      </li>
    </ul>

  </div>
</template>

<script>

// import Folders from './components/Folders.vue'
const axios = require('axios').default;


export default {
  name: 'app',
  components: {

  },
  data: function () {
    return {
      selfURL: globals.selfURL,
      files: globals.files
    }
  },
  created: function () {

  
  },
  methods: {

    onMakeBackup: function () {

      console.log('--- click');

      axios.get( this.selfURL+'&task=make&action=database',
        {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
      ).then(function(response){
        
        console.log('SUCCESS!!', response);

        if ( response.data ) {

          console.log(response.data);

        }
      })
      .catch(function(){
        console.log('FAILURE!!');
      })
      .finally(function () {
        // always executed
        //that.clearFileUpload();
      }); 



    }
  }
}
</script>

<style>


</style>
