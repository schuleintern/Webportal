<template>
  <div id="app" class="flex-row">



    <AdminACL></AdminACL>

    <div class="flex-1">
 
      <div>
        <h3><i class="fa fas fa-users-cog"></i>Hauptadministratoren</h3>
        <ul>
          <li v-bind:key="index" v-for="(item, index) in globalAdminGroup">
            {{item.name}} [{{item.userType}}]
          </li>
        </ul>
      </div>

      <AdminGroupExtension
        v-bind:users="extensionAdminGroup"
      ></AdminGroupExtension>

    </div>

    
  </div>
</template>

<script>
//console.log('globals',globals);

//const axios = require('axios').default;

import AdminACL from './components/adminACL.vue'
import AdminGroupExtension from './components/adminGroupExtension.vue'

export default {
  name: 'app',
  props: {
  },
  components: {
    AdminGroupExtension,
    AdminACL
  },
  data: function () {
    return {
      // selfURL: globals.selfURL,
      // loading: false,
      // error: false,
      // succeed: false,

      //acl: globals.acl,
      globalAdminGroup: globals.globalAdminGroup,
      extensionAdminGroup: globals.extensionAdminGroup
    }
  },

  created: function () {

},
  methods: {


  }
}
</script>

<style>
</style>
