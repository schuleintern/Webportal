<template>
  <div class="folders">
    Folders
    <ul>
      <li v-bind:key="index" v-for="(item, index) in folders"
        @click="clickHandler(item)"  >
        {{item.isStandardFolder}}
        {{item.folderName}}
        {{item.folderID}}
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

    clickHandler: function (item) {

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
