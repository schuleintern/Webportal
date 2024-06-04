<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="flex-1">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
      </div>
      <div class="flex-1 flex-row flex-end">
      </div>
    </div>

    {{ form }}

    <hr>

    {{ form.childs }}


  </div>

</template>

<script>

export default {
  name: 'ItemComponent',
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false
    };
  },
  props: {
    acl: Array,
    item: []
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