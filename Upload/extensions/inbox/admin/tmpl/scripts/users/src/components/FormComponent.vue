<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="flex-1">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i>
          Zurück
        </button>
        <button class="si-btn" @click="handlerSaveForm()"><i class="fa fa fa-save"></i> Speichern</button>
      </div>
      <div class="flex-1 flex-row flex-end">
        <button class="si-btn si-btn-light" @click="handlerDelete()" v-if="deleteBtn === false"><i
            class="fa fa fa-trash"></i> Löschen
        </button>
        <button class="si-btn si-btn-red" @click="handlerDeleteDo(item)" v-if="deleteBtn === true"><i
            class="fa fa fa-trash"></i> Wirklich Löschen ?
        </button>
      </div>
    </div>

    <div class="si-form flex-row">

      <ul class="flex-5">
        <li :class="required">
          <label>Title</label>
          <input type="text" v-model="form.title" required>
        </li>


        <li v-bind:key="index" v-for="(item, index) in  form.childs" class="margin-b-s margin-r-s blockInline">
          <User v-if="item.user" v-bind:data="item.user"></User>
          <UserSelect maxAnzahl="1" @submit="handelerUser(item, $event)" :preselected="item.user"></UserSelect>


        </li>
        <li>
          <button class="si-btn" @click="handlerAddLine"><i class="fa fa-plus"></i>Neue Benutzer*in</button>
        </li>


      </ul>


    </div>

  </div>
</template>

<script>

import User from '../mixins/User.vue'
import UserSelect from '../mixins/UserSelect.vue'

export default {
  name: 'ItemComponent',
  components: {
    UserSelect, User
  },
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false
    };
  },
  setup() {

  },
  props: {
    acl: Array,
    item: []
  },
  created: function () {
    this.form = this.item;
  },
  methods: {

    handelerUser: function (item, select) {

      if (select[0]) {
        item.user = select[0]
        item.user_id = item.user.id
      }

    },
    handlerAddLine: function () {
      if (!this.form.childs) {
        this.form.childs = [];
      }
      this.form.childs.push({
        inbox_id: this.form.id,
        user_id: false
      });
    },
    handelerUsers: function (data) {
      this.form.childs = data;
    },
    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },

    handlerSaveForm() {

      if (!this.form.title) {
        this.required = 'required';
        return false;
      }

      var that = this;
      this.$bus.$emit('item--submit', {
        item: this.form,
        callback: function (data) {
          that.item.id = data.id;
          that.required = '';
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

<style></style>