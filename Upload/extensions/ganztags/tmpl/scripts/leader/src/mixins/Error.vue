<template>
  <div>

    <div v-show="error" class="si-error">
      <div className="head">Error:</div>
      <div className="msg">{{ error }}</div>
    </div>

  </div>
</template>

<script>

export default {
  data() {
    return {
      interval: false
    };
  },
  props: {
    error: Boolean | String
  },
  created: function () {

  },
  watch: {
    error: function (newVal, oldVal) {
      if (newVal != false) {
        this.interval = window.setTimeout(this.close, 3000);
      } else {
        window.clearTimeout(this.interval);
      }
    }
  },
  methods: {
    close: function () {
      this.error = false;
    }
  }


};
</script>

<style>

</style>