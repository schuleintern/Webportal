<template>
  <div>
    <button v-if="status == 1" class="si-btn si-btn-toggle-on" :class="{ 'si-btn-hide': parseInt(disable) === 0, 'si-btn-curser-off' : disable == false }"
      v-on:click="handlerClick">
      <i class="fa fas fa-toggle-on"></i> {{label_true}}</button>
    <button v-else class="si-btn si-btn-toggle-off" :class="{ 'si-btn-hide': parseInt(disable) === 0, 'si-btn-curser-off' : disable == false }"
      v-on:click="handlerClick">
      <i class="fa fas fa-toggle-off"></i> {{label_false}}</button>
  </div>
</template>

<script>

export default {
  data() {
    return {
      status: 0,
      label_true: 'An',
      label_false: 'Aus'
    };
  },
  props: {
    input: Number,
    disable: Number,
    labelTrue: String,
    labelFalse: String
  },
  created: function () {
    this.status = parseInt(this.input);
    if (this.labelTrue) {
      this.label_true = this.labelTrue;
    }
    if (this.labelFalse) {
      this.label_false = this.labelFalse;
    }
  },
  watch: {
    input: function () { 
      this.status = parseInt(this.input);
    }
  },
  methods: {
    handlerClick: function () {
      //console.log(this.disable)
      if (this.disable == false || this.disable == 0 || this.disable == 'false') { // AUS
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

<style></style>