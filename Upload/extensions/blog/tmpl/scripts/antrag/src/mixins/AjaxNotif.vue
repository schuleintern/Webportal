<template>
  <div>

    <div v-show="open" class="si-succeed"  >
      <div class="msg">{{ notif }}</div>
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
    notif: String
  },
  created: function () {

  },
  watch: {
    notif: function(newVal) {
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