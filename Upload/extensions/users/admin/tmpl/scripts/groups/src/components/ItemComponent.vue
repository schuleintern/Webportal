<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
        </button>
        <button class="si-btn" @click="handlerSubmit"><i class="fa fa-save"></i> Speichern</button>
      </div>
      <div v-show="form.id" class="flex flex-end">
        <button  v-on:click="handlerDelete" class="si-btn si-btn-light"><i class="far fa-trash-alt"></i> Löschen</button>
      </div>
    </div>

    <div class="width-70vw">
      <div class="si-form ">
        <ul class="flex-3">
          <li>
            <label>Title</label>
            <input type="text" v-model="form.title">
          </li>
        </ul>
        <div class="flex-row">
          <ul class="flex-3">
            <li>
              <label>Benutzer*innen</label>
              <UserSelect @submit="handelerUser(form, $event)" :preselected="form.users"></UserSelect>
              <div v-bind:key="index" v-for="(item, index) in  form.users" class="margin-b-s">
                <User :data="item" ></User>
              </div>
            </li>
          </ul>
          <ul class="flex-2">
            <li>
              <label>Status</label>
              <FormToggle :input="form.state" @change="handlerToggleState"></FormToggle>
            </li>
          </ul>
        </div>

      </div>
    </div>

  </div>

</template>

<script>

import FormToggle from '../mixins/FormToggle.vue'
import UserSelect from '../mixins/UserSelect.vue'
import User from "@/mixins/User.vue";

export default {
  name: 'ItemComponent',
  components: {
    User,
    UserSelect,
    FormToggle
  },
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false,

      start_a: '00',
      start_b: '00',
      end_a: '00',
      end_b: '00'
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

    handlerDelete() {

      if (!this.form.id) {
        return false;
      }
      if (confirm('Wirklich löschen?')) {
        this.$bus.$emit('item--delete', {
          item: this.item
        });
      }


    },
    handelerUser: function (form, event) {

      //console.log(form,event)

      form.users = event;
    },
    handlerEnd: function () {
      this.form.end = this.end_a + ':' + this.end_b;
    },
    handlerStart: function () {
      this.form.start = this.start_a + ':' + this.start_b;
    },
    handlerToggleState: function (data) {
      this.form.state = data.value;
      //console.log(val)
    },
    handlerSubmit: function () {
      this.$bus.$emit('item--submit', this.form);
    },
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