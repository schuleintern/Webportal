<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <button class="si-btn si-btn-light" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
      <button class="si-btn si-btn-light" @click="handlerDelete()" v-if="deleteBtn === false"><i class="fa fa fa-trash"></i> Löschen</button>
      <button class="si-btn si-btn-red" @click="handlerDeleteDo(item)" v-if="deleteBtn === true"><i class="fa fa fa-trash"></i> Wirklich Löschen ?</button>
    </div>

    {{ form }}
    <form class="si-form" @change="handlerSaveForm($event)">
      <ul>
        <li :class="required">
          <label>Title</label>
          <input type="text" v-model="form.title">
        </li>
        <li class="">
          <label>state</label>
          <input type="text" v-model="form.state">
        </li>
        <li class="">
          <label>Sort</label>
          <input type="text" v-model="form.sort">
        </li>
        <li class="">
          <label>Farbe</label>
          <input type="text" v-model="form.color">
        </li>
        <li class="">
          <label>Vorausgewählt</label>
          <input type="text" v-model="form.preSelect">
        </li>
        <li class="">
          <label>Ferien</label>
          <input type="text" v-model="form.ferien">
        </li>
        <li class="">
          <label>ICS Veröffentlichen</label>
          <input type="text" v-model="form.public">
        </li>


      </ul>

    </form>

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

    handlerSaveForm() {

      if (!this.item.title) {
        this.required = 'required';
        return false;
      }

      var that = this;
      this.$bus.$emit('item--submit', {
        item: this.form,
        callback: function (data) {
          that.item.id = data.id;
        }
      });
      return false;
    },

    handlerDelete() {
      this.deleteBtn = true;
    },

    handlerDeleteDo(item) {

      this.$bus.$emit('item--delete', {
        item: item
      });

    }

  }


};
</script>

<style>

</style>