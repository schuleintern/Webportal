<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">

        <Item v-bind:data="data"></Item>

      </div>
    </div>

  </div>
</template>

<script>



import Item from '../components/Item.vue'

export default {

  components: {
    Item
  },
  data() {
    return {
      open: false
    };
  },
  props: {
      data: Object
  },
  created: function () {

  },
  mounted() {
    // access our input using template refs, then focus
  },
  watch: {
    data: function(newVal, oldVal) {
      if (newVal == false) {
        this.open = false;
      } else {
        this.open = true;
      }
    }
  },
  methods: {
    handlerClose: function () {
      this.data = false;
      this.$emit('close');
    }
  }


};
</script>

<style>

</style>