<template>
  <div className="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div className="si-modal-box">
      <button className="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div className="si-modal-content">

       {{item}}
        <hr>
        <UserSelect></UserSelect>

      </div>
    </div>

  </div>
</template>

<script>

import UserSelect from './../mixins/UserSelect.vue'

export default {

  components: {
    UserSelect
  },
  data() {
    return {
      open: false,
    };
  },
  props: {
    item: Object
  },
  created: function () {
    var that = this;
    this.$bus.$on('modal-form--open', () => {
      that.open = true;
    });
    this.$bus.$on('modal-form--close', () => {
      that.open = false;
    });
  },
  methods: {
    handlerClose: function () {
      this.$bus.$emit('modal-form--close');
    }
  }


};
</script>

<style>

</style>