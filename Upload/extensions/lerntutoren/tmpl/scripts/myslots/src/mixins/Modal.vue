<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">

        <Form @formsubmit="handlerFormSubmit" v-bind:data="data"></Form>

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
    },
    handlerFormSubmit: function (data) {
      //console.log(data)
      this.$emit('formsubmit', data)
    }
  }


};
</script>

<style>

</style>