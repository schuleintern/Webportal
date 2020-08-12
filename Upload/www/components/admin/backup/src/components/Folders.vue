<template>
  <div class="folders flex-2">

    <div class="toolbar">
      <button class="btn btn-blau"
          @click="clickHandlerNewMessage()">Neue Nachricht</button>
    </div>
    
    <ul>
      <li v-bind:key="index" v-for="(item, index) in folders">
        <button class=""
          @click="clickHandlerFolder(item)"
          :class="{
            'btn btn-blau' : item.folderName == 'Posteingang',
            'btn btn-gruen' : item.folderName == 'Gesendete',
            ' btn-info' : item.folderName == 'Archiv',
            ' btn-danger' : item.folderName == 'Papierkorb',
            'btn btn-grau' : item.isStandardFolder == false
          }" >
          {{item.folderName}}
        </button>
      </li>
    </ul>

  </div>
</template>

<script>
export default {
  name: 'Folders',
  props: {
    folders: Object
  },
  created: function () {

    EventBus.$emit('folders--get', {});

  },
  methods: {

    clickHandlerNewMessage: function () {

      EventBus.$emit('message--form', {
        //folder: item,
      })

    },

    clickHandlerFolder: function (item) {

      EventBus.$emit('messages--changeFolder', {
        folder: item,
      })

    }

  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
