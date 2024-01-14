<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <button class="si-btn si-btn-light" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
    </div>

    <div class="si-form" >
      <ul>
        <li :class="required">
          <label>Benutzer*in:</label>
          <User v-if="item.user" :data="item.user"></User>
        </li>
        <li>
          <label>Gemeldet Von:</label>
          <User v-if="form.by" :data="form.by"></User>
          <UserSelect @submit="handlerBySelect" :preselected="form.by" maxAnzahl="1"></UserSelect>
        </li>
        <li >
          <label>Info:</label>
          <textarea v-model="form.info"></textarea>
        </li>

        <li>
          <button class="si-btn" @click="handlerSaveForm"><i class="fa fa-save"></i> Speichern</button>
        </li>

      </ul>

    </div>

  </div>

</template>

<script>

import UserSelect from './../mixins/UserSelect.vue'
import User from './../mixins/User.vue'


export default {
  name: 'FormComponent',
  components: {
    UserSelect, User
  },
  data() {
    return {
      form: {
        by: false,
        info: ''
      },
      required: ''
    };
  },
  props: {
    acl: Array,
    item: []
  },
  created: function () {
    //this.form = this.item;
  },
  methods: {

    handlerBySelect: function (data) {
      if (data[0] && data[0].id) {
        this.form.by = data[0];
      }
    },
    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },

    handlerSaveForm() {

      if (!this.item.user) {
        this.required = 'required';
        return false;
      }

      this.$bus.$emit('item--submit-form', {
        item: this.item,
        form: this.form
      });
      return false;
    },


  }


};
</script>

<style>

</style>