<template>
  <div>
    <div class="si-btn-multiple">
      <button v-bind:key="index" v-for="(item, index) in options"
              class="si-btn si-btn-border margin-r-s"
              :class="{'si-btn-active': isActive(item) }"
              @click="handlerClick(item)"
              >{{item.title}}</button>
    </div>
  </div>
</template>

<script>

export default {
  data() {
    return {
      value: []
    };
  },
  props: {
    input: Text,
    options: Array,
    disable: Number
  },
  created: function () {
    if (this.input) {
      this.value = String(this.input).split(',');
    }
    if (!this.value || typeof this.value !== 'object') {
      this.value = [];
    }
    this.value = this.value.map(function (x) {
      return parseInt(x, 10);
    });
  },
  methods: {
    isActive(item) {
      if (this.value) {
        if (this.value.includes(item.value)) {
          return true;
        }
      }
      return false;
    },
    handlerClick:function (item) {

      if (this.disable == 0) { // AUS
        return false;
      }
      const val = item.value;
      if (this.value && this.value.includes(val)) {
        this.value.splice(this.value.indexOf(val), 1)
      } else {
        this.value.push(val);
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