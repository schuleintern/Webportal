<template>

  <div>

    <div>
      <label>Anzahl der Sanktionen:</label>
      {{count}}
    </div>

    <div v-if="text" class="si-box-border">
      <div v-html="text"></div>
    </div>



  </div>
</template>

<script>


export default {

  name: 'App',
  components: {
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      acl: window.globals.acl,
      count: window.globals.count,
      text: window.globals.text


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
