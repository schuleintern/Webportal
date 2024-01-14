<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>


    <ListComponent v-if="page === 'list'" :acl="acl" :list="list" ></ListComponent>
    <ItemComponent v-if="page === 'item'" :acl="acl" :item="item" ></ItemComponent>


  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

import ListComponent from './components/ListComponent.vue'
import ItemComponent from './components/ItemComponent.vue'

const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    AjaxError, AjaxSpinner,
    ListComponent, ItemComponent
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      acl: window.globals.acl,
      error: false,
      loading: false,
      page: 'list',

      list: false,
      item: []

    };
  },
  created() {

  },
  methods: {


  }
}
</script>

<style>

</style>
