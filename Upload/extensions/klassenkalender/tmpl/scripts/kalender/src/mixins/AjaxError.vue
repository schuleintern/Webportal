<template>
  <div>

    <div v-show="open" class="si-error"  >
      <div class="head">Error:</div>
      <div class="msg">{{ msg }}</div>
    </div>

  </div>
</template>

<script>

export default {
  data() {
    return {
      interval: false,
      open: false,
      msg: false
    };
  },
  props: {
    error: String
  },
  created: function () {

    this.$bus.$on('error', data => {

      if (data.msg) {
        this.open = true;
        this.msg = data.msg;
        this.interval = window.setTimeout(this.close,3000);
      }

    });

  },
  watch: {
    error: function(newVal) {
      this.msg = newVal;
      this.open = true;
      this.interval = window.setTimeout(this.close,3000);
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