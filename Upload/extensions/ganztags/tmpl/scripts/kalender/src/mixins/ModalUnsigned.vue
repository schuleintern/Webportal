<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">


        <h4>Sollen die fehlenden {{unsigned.count}} Schüler hinzugefügt werden?</h4>

        <button  class="si-btn" v-on:click="handlerOpenUnsigned()"><i class="fa fa-plus"></i> Ok</button>


      </div>
    </div>

  </div>
</template>

<script>


export default {

  components: {

  },
  data() {
    return {
      open: false
    };
  },
  props: {
    item: Object,
    unsigned: Object
  },
  created: function () {
    var that = this;
    EventBus.$on('modal-unsigned--open', data => {
      /*if (data.item) {
        that.item = data.item;
      }*/

      that.open = true;
    });
    EventBus.$on('modal-unsigned--close', data => {
      that.open = false;
    });
  },
  methods: {
    handlerClose: function () {
      EventBus.$emit('modal-unsigned--close');
    },
    handlerOpenUnsigned: function () {
      this.handlerClose();
      EventBus.$emit('unsigned--merge');
    }
  }


};
</script>

<style>

</style>