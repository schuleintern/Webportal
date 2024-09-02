<template>

  <div class="">
    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
      </div>
      <div class="">
      </div> 
    </div>



    <div class="">

      <h3>{{ form.title }}</h3>

      <UmfragenResult :form="form"></UmfragenResult>


    </div>
  </div>

</template>

<script>


import UmfragenResult from '../mixins/UmfragenResult.vue'

export default {
  name: 'AnswerComponent',
  components: {
    UmfragenResult
  },
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false
    };
  },
  props: {
    acl: Array,
    item: [],
    answers: Array
  },
  created: function () {
    this.form = this.item;
  },
  methods: {


    
    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },

  }


};
</script>

<style>

</style>