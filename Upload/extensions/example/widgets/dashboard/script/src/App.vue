<template>
  <div>

    HIER IST VUE!!!!!
    <button class="si-btn" v-on:click="handlerClick">Hier</button>

  </div>
</template>

<script>

export default {
  components: {
  },
  data() {
    return {


    };
  },
  created: function () {



  },
  mounted() {


  },
  methods: {

    handlerClick: function () {
      console.log('Do Something!!!');
    }

  }

};
</script>

<style>

</style>