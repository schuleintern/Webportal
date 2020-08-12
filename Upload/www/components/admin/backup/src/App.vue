<template>
  <div id="app" class="flex">
hier

    <ul>
      <li>
        <ul>
          <li>filename</li>
          <li>size</li>
          <li>mtime</li>
        </ul>
      </li>
      <li v-bind:key="index" v-for="(entry, index) in  files">
        <ul>
          <li>{{entry.filename}}</li>
          <li>{{entry.size}}</li>
          <li>{{entry.mtime}}</li>
        </ul>
      </li>
    </ul>

  </div>
</template>

<script>

// import Folders from './components/Folders.vue'

export default {
  name: 'app',
  components: {

  },
  data: function () {
    return {
      files: globals.files
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
