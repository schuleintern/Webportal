<template>
  <div class="si-btn-multiple">
    <button class="si-btn si-btn-toggle-off" :class="{ 'si-btn-toggle-on': status === 'w' }"
      v-on:click="handlerClick('w')">
      <i class="fa fas fa-venus"></i> weiblich</button>
    <button class="si-btn si-btn-toggle-off" :class="{ 'si-btn-toggle-on': status === 'm' }"
      v-on:click="handlerClick('m')">
      <i class="fa fas fa-mars"></i> männlich</button>
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
    input: Number
  },
  created: function () {
    this.status = this.input;
  },
  watch: {
    input: function () { 
      this.status = this.input;
    }
  },
  methods: {
    handlerClick: function (value) {

      this.status = value;

      this.$emit('change', {
        value: this.status
      });
    }
  }


};
</script>

<style></style>