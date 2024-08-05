<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <button class="si-btn si-btn-light" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
      <button class="si-btn si-btn-light" @click="handlerDelete()" v-if="deleteBtn === false"><i class="fa fa fa-trash"></i> Löschen</button>
      <button class="si-btn si-btn-red" @click="handlerDeleteDo(item)" v-if="deleteBtn === true"><i class="fa fa fa-trash"></i> Wirklich Löschen ?</button>
    </div>

    <div class="si-form">
      <ul>
        <li :class="required">
          <label>Titel</label>
          <input type="text" v-model="form.title">
        </li>
        <li  :class="required" class="">
          <label>Kurztitel</label>
          <input type="text" v-model="form.short">
        </li>
        <li  :class="required" class="">
          <label>Öffentlich
            <div class="text-small">Termine sind für Eltern und Schüler*innen sichtbar.</div>
          </label>
          <div class="margin-l-l">
            <FormToggle :input="form.isPublic" @change="triggerToggleValue($event, 'isPublic')"></FormToggle>
          </div>
        </li>
        <li>
          <button v-on:click="handlerSaveForm" class="si-btn width-100p"><i class="fa fa-save"></i> Speichern</button>
        </li>
      </ul>

    </div>

  </div>

</template>

<script>

import FormToggle from './../mixins/FormToggle.vue'


export default {
  name: 'ItemComponent',
  components: {
    FormToggle
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
    item: []
  },
  created: function () {
    this.form = this.item;
  },
  methods: {


    triggerToggleValue(data, item) {
      this.form[item] = data.value;
    },
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