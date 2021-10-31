<template>
  <div id="app" class="flex-row">



    <AdminACL></AdminACL>

    <div class="flex-1">
 
      <div class="margin-b-l">
        <h3><i class="fa fas fa-users-cog margin-r-m"></i>Hauptadministratoren</h3>
        <div class="padding-l-l">
          <ul class="noListStyle">
            <li v-bind:key="index" v-for="(item, index) in globalAdminGroup"
              class="line-oddEven padding-t-s padding-b-s padding-l-l">
              {{item.name}}
              <span class="text-small text-grey">({{item.userType}})</span>
            </li>
          </ul>
        </div>
      </div>

      <AdminGroupExtension
        v-bind:users="extensionAdminGroup"
      ></AdminGroupExtension>

    </div>

    
  </div>
</template>

<script>

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
