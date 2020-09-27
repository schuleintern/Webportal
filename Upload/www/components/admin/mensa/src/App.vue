<template>
  <div id="app">
    


    <Acl v-bind:moduleID="false"
        v-bind:childID="false"
        v-bind:moduleName="moduleName"></Acl>


  </div>
</template>

<script>

//const axios = require('axios').default;

import Acl from './components/Acl.vue'

export default {
  name: 'app',
  components: {
    Acl
  },
  data: function () {
    return {

      moduleName: 'mensaSpeiseplan',

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
