<template>
  <div v-if="loading == true" class="overlay">
    <i class="fa fas fa-sync-alt fa-spin"></i>
  </div>
</template>

<script>

export default {
  data() {
    return {
    };
  },
  props: {
    loading: String
  },
  created: function () {

  },
  methods: {

  }


};
</script>

<style>

</style>