<template>
  <div>
    <div class="si-btn-multiple">
      <button v-bind:key="index" v-for="(item, index) in options"
              class="si-btn si-btn-border margin-r-s"
              :class="{'si-btn-active': item.value == value}"
              @click="handlerClick(item)"
              >{{item.title}}</button>
    </div>
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
    if (!this.value) {
      this.value = 0;
    }
  },
  methods: {
    handlerClick:function (item) {

      if (this.disable == 0) { // AUS
        return false;
      }
      this.value = item.value;
      this.$emit('submit', {
        value: this.value
      });

    }
  }


};
</script>

<style>

</style>