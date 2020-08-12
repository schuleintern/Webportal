<template>
  <div id="app" class="flex">

    <ul class="flex noListStyle">
      <li>
        <ul class="flex-row noListStyle text-bold">
          <li class="flex-1">Dateiname</b></li>
          <li class="flex-1">Größe</li>
          <li class="flex-1">Änderungsdatum</li>
          <li class="flex-1">Donwload</li>
        </ul>
      </li>
      <li v-bind:key="index" v-for="(entry, index) in  files"
        class="line-oddEven">
        <ul class="flex-row noListStyle text-lineHeight-m">
          <li class="flex-1">{{entry.filename}}</li>
          <li class="flex-1">{{entry.formatSize}}</li>
          <li class="flex-1">{{entry.formatMtime}}</li>
          <li class="flex-1">
            <a v-bind:href="selfURL+'&task=2&path='+entry.filepath">Download</a>
          </li>
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
      selfURL: globals.selfURL,
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
