<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">

        <Form v-if="dates" v-bind:dates="dates" v-bind:room="room"></Form>

      </div>
    </div>

  </div>
</template>

<script>



import Form from '../components/Form.vue'


export default {

  components: {
    Form
  },
  data() {
    return {
      open: false
    };
  },
  props: {
    dates: Object,
    room: String
  },
  created: function () {
    var that = this;
    EventBus.$on('modal-form--open', data => {
      that.dates = data.dates;
      that.open = true;
    });
    EventBus.$on('modal-form--close', data => {
      that.open = false;
    });
  },
  methods: {
    handlerClose: function () {
      EventBus.$emit('modal-form--close');
    }
  }


};
</script>

<style>

</style>