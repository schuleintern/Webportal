<template>
  <div>
    <button v-if="status == 1" class="si-btn si-btn-toggle-on" :class="{'si-btn-hide': parseInt(disable) === 0  }"   v-on:click="handlerClick">
      <i class="fa fas fa-toggle-on"></i> An</button>
    <button v-else class="si-btn si-btn-toggle-off" :class="{'si-btn-hide': parseInt(disable) === 0  }" v-on:click="handlerClick">
      <i class="fa fas fa-toggle-off"></i> Aus</button>
  </div>
</template>

<script>

export default {
  data() {
    return {
      status: 0
    };
  },
  props: {
    input: Number,
    disable: Number
  },
  created: function () {
    this.status = this.input;
  },
  methods: {
    handlerClick: function () {
      if (this.disable == 0) { // AUS
        return false;
      }
      if (this.status == 1) {
        this.status = 0;
      } else {
        this.status = 1;
      }
      this.$emit('change', {
        value: this.status
      });
    }
  }


};
</script>

<style>

</style>