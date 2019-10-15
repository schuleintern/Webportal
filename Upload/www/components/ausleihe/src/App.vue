<template>
  <div id="app">
    HIER123
  </div>
</template>

<script>


//console.log('globals',globals);
var globals = false;
globals = globals || {
  userID:  1608
};

//import Folders from './components/Folders.vue'

const axios = require('axios').default;

axios.defaults.headers.common['x-authorization'] = '112233' // for all requests

export default {
  name: 'app',
  components: {
    // Folders,
    // Messages,
    // Message
  },
  data: function () {
    return {
      folders: {},
      messages: []
    }
  },
  created: function () {


    // var that = this;

    // EventBus.$on('folders--get', data => {

    //   that.ajaxGet(
    //     //'rest.php/GetMsgFolders/'+globals.userID,
    //     './../testjson/GetMsgFolders.json',
    //     {},
    //     function (response, that) {
    //       that.folders = response.data;
    //     }
    //   );

    // });

    // EventBus.$on('messages--changeFolder', data => {

    //   console.log('cghange msg folder',data.folder.folderName);

    //   var url = 'rest.php/GetMsgMessages/'+globals.userID;

    //   if (data.folder.isStandardFolder && !data.folder.folderID) {
    //     url += '/'+data.folder.folderName;
    //   } else {
    //     url += '/'+data.folder.folderID;
    //   }
    //   that.ajaxGet(
    //     //url,
    //     './../testjson/GetMsgMessages.json',
    //     {},
    //     function (response, that) {
    //       that.messages = response.data;
    //     }
    //   );

    // });

    // EventBus.$emit('messages--changeFolder', {
    //   folder: {
    //     isStandardFolder: true,
    //     folderName: 'posteingang',
    //     folderID: 0
    //   },
    // })

    
  },
  methods: {


    ajaxGet: function (url, params, callback, error, allways) {

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
      .catch(function (error) {
        //console.log(error);
        if (error && typeof error === 'function') {
          error(error);
        }
      })
      .finally(function () {
        // always executed
        if (allways && typeof allways === 'function') {
          allways();
        }
      });  
      
    }
  }
}
</script>

<style>
</style>
