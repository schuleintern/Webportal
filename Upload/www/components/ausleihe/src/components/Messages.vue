<template>
  <div class="messages">
    Messages

    <div id="demo">
      <form id="search">
        Search <input name="query" v-model="searchQuery">
      </form>
      <GridTemplate
        v-bind:list="messages"
        v-bind:columns="gridColumns"
        v-bind:filter-key="searchQuery">
      </GridTemplate>
    </div>

  </div>
</template>

<script>

import GridTemplate from './GridTemplate.vue'

export default {
  name: 'Messages',
  components: {
    GridTemplate
  },
  props: {
    messages: Array
  },
  data: function () {
    return {
      searchQuery: '',
      gridColumns: ['subject', 'senderName','timeFormat']
    }
  },
  
  created: function () {

    


  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
