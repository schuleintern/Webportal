<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
      <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
      <button class="si-btn " @click="handlerSaveForm()"><i class="fa fa fa-save"></i> Speichern</button>
    </div>
    <div class="">
      <button class="si-btn si-btn-light" @click="handlerDelete()" v-if="deleteBtn === false"><i class="fa fa fa-trash"></i> Löschen</button>
      <button class="si-btn si-btn-red" @click="handlerDeleteDo(item)" v-if="deleteBtn === true"><i class="fa fa fa-trash"></i> Wirklich Löschen ?</button>
    </div> 
  </div>


    <div class="si-form">
      <ul>
        <li :class="required" class="flex-row">
          <div class="flex-5 flex margin-r-l">
            <label>Titel</label>
            <input type="text" v-model="form.title">
          </div>
          <div class="flex-1 flex">
            <label>Status</label>
          <FormToggle :input="form.state" @change="handlerState"></FormToggle>
          </div>
        </li>
        <li>
          <UserSelect @submit="handelerUserlist" :preselected="form.userlist"></UserSelect>
          <div v-if="form.userlist" class="padding-t-s">
            <span v-bind:key="index" v-for="(item, index) in  form.userlist" class="margin-b-s margin-r-s blockInline">
              <User v-bind:data="item"></User>
            </span>
          </div>

        </li>
      </ul>
    </div>
    <div class="si-form">  
      <ul>
        <li v-bind:key="index" v-for="(item, index) in  form.childs"  class="flex-row">
          <div class="flex-1">
            <label>Sortierung</label>
            <input type="text" v-model="item.sort" class="width-7rem" >
          </div>
          <div class="flex-5 flex margin-r-l">
            <label>{{ index+1 }}. Frage</label>
            <input type="text" v-model="item.title">
          </div>
          <div class="flex-1 flex">
            <label>Typ</label>
            <select v-model="item.typ">
              <option value="boolean">Ja/Nein</option>
              <option value="text">Text</option>
              <option value="number">Zahl</option>
            </select>
          </div>
        </li>
        <li>
          <button class="si-btn si-btn-green " @click="handlerNewItem()"><i class="fa fa-plus"></i> Neue Frage</button>
        </li>
      </ul>

    </div>

  </div>

</template>

<script>

import FormToggle from '../mixins/FormToggle.vue'
import UserSelect from '../mixins/UserSelect.vue'
import User from '../mixins/User.vue'

export default {
  name: 'ItemComponent',
  components: {
    FormToggle, UserSelect, User
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

    handelerUserlist(data) {
      this.form.userlist = data;
    },
    handlerNewItem() {

      if (!this.form.childs) {
        this.form.childs = [];
      }
      this.form.childs.push({
        'title': '',
        'typ': 'string'
      });

    },
    handlerState(data) {
      this.form.state = data.value; 
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
        callback: function () {

          /*
          if (data.id) {
            that.item.id = data.id;
          }
          */
         that.handlerBack();
          
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