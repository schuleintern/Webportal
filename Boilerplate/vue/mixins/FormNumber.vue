<template>
  <div>
    <input type="number" v-model="value" v-on:change="handlerToggle" />
  </div>
</template>

<script>

export default {
  data() {
    return {
      value: 0
    };
  },
  props: {
    input: Number,
    disable: Number
  },
  created: function () {
    this.value = this.input;
  },
  methods: {
    handlerToggle: function () {
      if (this.disable == 0) { // AUS
        return false;
      }
      this.$emit('submit', {
        value: parseInt(this.value)
      });
    }
  }


};
</script>

<style>

</style>