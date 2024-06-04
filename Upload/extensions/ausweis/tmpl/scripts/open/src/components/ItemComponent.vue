<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="flex-1">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
      </div>
      <div class="flex-1 flex-row flex-end">
      </div>
    </div>


    <div class="si-details">
      <ul>
        <li>
          <label>Status</label>
          <button v-if="form.state == 1" class="si-btn si-btn-off width-20rem" >Offen</button>
          <button v-if="form.state == 2" class="si-btn si-btn-off width-20rem" >Freigegeben</button>
        </li>
        <li>
          <label>Datum</label>
          {{ form.createdTime }}
        </li>
        <li>
          <label>Benutzer*inn</label>
          {{ form.user.name }}
        </li>
        <li>
          <label>Foto</label>
          <img :src="form.image" width="100"/>
        </li>
        <li v-if="form.doneTime">
          <label>Genehmigt</label>
          {{ form.doneTime }}
        </li>
        <li v-if="form.doneUserUser">
          <label>Von</label>
          {{ form.doneUserUser.name }}
        </li>
      </ul>
    </div>





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