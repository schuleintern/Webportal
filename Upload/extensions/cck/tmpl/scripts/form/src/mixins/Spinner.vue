<template>
  <div v-if="loading == true" class="overlay text-white">
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
    loading: Boolean
  },
  created: function () {

  },
  methods: {

  }


};
</script>

<style>

</style>