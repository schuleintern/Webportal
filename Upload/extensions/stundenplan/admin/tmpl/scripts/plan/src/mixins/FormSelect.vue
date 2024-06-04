<template>
  <div>
    <select v-model="value" v-on:change="handlerToggle" >
      <option v-bind:key="index" v-for="(item, index) in options" v-bind:value="item.value">{{item.title}}</option>
    </select>
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
    input: Text,
    options: Array,
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
        value: this.value
      });
    }
  }


};
</script>

<style>

</style>