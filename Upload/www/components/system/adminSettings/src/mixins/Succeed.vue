<template>
  <div>

    <div v-show="succeed" class="si-succeed">
      <div class="msg">{{ succeed }}</div>
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
    succeed: String
  },
  created: function () {

  },
  watch: {
    succeed: function(newVal, oldVal) {
      if (newVal != false) {
        this.interval = window.setTimeout(this.close,3000);
      } else {
        window.clearTimeout(this.interval);
      }
    }
  },
  methods: {
    close: function () {
      this.succeed = false;
    }
  }


};
</script>

<style>

</style>