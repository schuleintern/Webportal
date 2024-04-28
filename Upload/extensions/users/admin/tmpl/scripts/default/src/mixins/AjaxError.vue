<template>
  <div>

    <div v-show="open" class="si-error"  >
      <div class="head">Error:</div>
      <div class="msg">{{ error }}</div>
    </div>

  </div>
</template>

<script>

export default {
  data() {
    return {
      interval: false,
      open: false
    };
  },
  props: {
      error: String
  },
  created: function () {

  },
  watch: {
    error: function(newVal) {
      if (newVal != false) {
        this.open = true;
        this.interval = window.setTimeout(this.close,3000);
      } else {
        window.clearTimeout(this.interval);
      }
    }
  },
  methods: {
    close: function () {
      this.open = false;
    }
  }


};
</script>

<style>

</style>