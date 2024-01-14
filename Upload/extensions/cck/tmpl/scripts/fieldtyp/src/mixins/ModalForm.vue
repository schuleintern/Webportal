<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">

        <Form  v-bind:item="getItem()"></Form>

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
      open: false,
      form: false
    };
  },
  props: {
    item: Object
  },
  computed: {

  },
  created: function () {
    var that = this;
    EventBus.$on('modal-form--open', data => {
      that.form = false;
      if (data && data.item) {
        that.form = data.item;
      }
      that.open = true;
    });
    EventBus.$on('modal-form--close', data => {
      that.open = false;
    });
  },
  methods: {
    getItem: function () {
      if (this.form) {
        return this.form;
      }
      return this.item;
    },
    handlerClose: function () {
      EventBus.$emit('modal-form--close');
    }
  }


};
</script>

<style>

</style>