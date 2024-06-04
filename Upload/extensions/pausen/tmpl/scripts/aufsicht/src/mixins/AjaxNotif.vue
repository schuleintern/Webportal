<template>
  <div>

    <div v-show="open" class="si-succeed"  >
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
    notif: String
  },
  created: function () {

    this.$bus.$on('notif--open', data => {

      if (data.msg) {
        this.open = true;
        this.msg = data.msg;
        this.interval = window.setTimeout(this.close,3000);
      }

    });

    this.$bus.$on('notif--close', () => {
      window.clearTimeout(this.interval);
    });

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