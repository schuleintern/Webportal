<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <button class="si-btn si-btn-light" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
    </div>

      <ul>
        <li >
          <label>Title</label>
          <div>{{form.title}}</div>
        </li>

      </ul>


  </div>

</template>

<script>

export default {
  name: 'ItemComponent',
  data() {
    return {
      form: {}
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