<template>
  <div class="messages flex-10">
    <div class="toolbar flex-row"> 
      <div class="flex-9">

          <button v-on:click="messageDelete()">Löschen</button>

          <button v-on:click="">Gelesen</button>
          <button v-on:click="">Ungelesen</button>


          <select v-model="messageMoveSelected">
            <option v-bind:key="index" v-for="(item, index) in folders"
              :value="item">{{item.folderName}}</option>
          </select>
          <button v-on:click="messageMove()">Verschieben</button>
          
        
      </div>
      <form id="search" class="3">
        <input type="search" name="query" v-model="searchQuery">
      </form>
    </div>

    <GridTemplate
      v-bind:list="messages"
      v-bind:columns="gridColumns"
      v-bind:columsHeader="gridColumnsHeader"
      v-bind:filter-key="searchQuery">
    </GridTemplate>

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
    messages: Array,
    folders: Array,
    messageMoveSelected: String
  },
  data: function () {
    return {
      searchQuery: '',
      gridColumns: ['hasAttachment','priority','isRead','subject', 'senderConnect','timeFormat'],
      gridColumnsHeader: ['','','','Betreff', 'Sender','Datum']
    }
  },
  
  created: function () {

  },
  methods: {

    messageMove: function () {

      EventBus.$emit('message--move', {
        toFolder: this.messageMoveSelected
      });

    },

    messageDelete: function () {

      EventBus.$emit('message--delete', {});

    }
    

  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
