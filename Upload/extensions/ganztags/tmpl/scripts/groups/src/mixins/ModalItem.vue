<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">

        <Item v-bind:item="item"></Item>

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
    item: Object
  },
  created: function () {
    var that = this;
    EventBus.$on('modal-item--open', data => {
      /*
      if (data.item) {
        that.item = data.item;
      }*/

      that.open = true;
    });
    EventBus.$on('modal-item--close', data => {
      that.open = false;
      //that.item = {};
    });
  },
  methods: {
    handlerClose: function () {
      EventBus.$emit('modal-item--close');
    }
  }


};
</script>

<style>

</style>