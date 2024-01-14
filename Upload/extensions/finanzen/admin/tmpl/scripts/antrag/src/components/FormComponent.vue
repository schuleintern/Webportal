<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="flex-1">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i>
          Zurück</button>
        <button class="si-btn" @click="handlerSaveForm()"><i class="fa fa fa-save"></i> Speichern</button>
      </div>
      <div class="flex-1 flex-row flex-end">
        <button class="si-btn si-btn-light" @click="handlerDelete()" v-if="deleteBtn === false"><i
            class="fa fa fa-trash"></i> Löschen</button>
        <button class="si-btn si-btn-red" @click="handlerDeleteDo(item)" v-if="deleteBtn === true"><i
            class="fa fa fa-trash"></i> Wirklich Löschen ?</button>
      </div>
    </div>

    <form class="si-form flex-row">

      <ul class="flex-5">
        <li :class="required">
          <label>Title</label>
          <input type="text" v-model="form.title" required>
        </li>
        <li :class="required">
          <label>Zahlungsempfänger</label>
          <input type="text" v-model="form.payee" required>
        </li>
        <li :class="required">
          <label>Benutzer*innen</label>
          <input type="hidden" v-model="form.users" required>
          <UserSelect @submit="handelerUsers" :preselected="form.users"></UserSelect>
          <div class="padding-t-s">
            <span v-bind:key="index" v-for="(item, index) in  form.users" class="margin-b-s margin-r-s blockInline">
              <User v-bind:data="item"></User>
            </span>
          </div>
        </li>

      </ul>

      <ul class="flex-3">
        <li :class="required">
          <label>Betrag</label>
          <div >
            <input type="text" v-model="form.amount_1" required class="width-12rem" placeholder="00">
          ,
          <input type="text" v-model="form.amount_2" required class="width-10rem"> EUR</div>
        </li>
        <li class="">
          <label>Fälligkeitsdatum</label>
          <Datepicker required :preview-format="format" :format="format" v-model="form.endDate"
                        :enableTimePicker="false" locale="de" cancel-text="Abbrechen"
                        select-text="Ok" :monthChangeOnScroll="false"></Datepicker>
        </li>
        <li class="">
          <label>Quittung</label>
          <input type="text" v-model="form.receipt">
          <input type="file" />
        </li>
      </ul>

    </form>

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


    const format = (val) => {
      return `${val.getDate()}.${val.getMonth() + 1}.${val.getFullYear()}`
    }

    return {
      format
    }
  },
  props: {
    acl: Array,
    item: []
  },
  created: function () {
    this.form = this.item;

    if (!this.form.amount_2) {
      this.form.amount_2 = "00";
    }
  },
  methods: {

    handelerUsers: function (data) {
      this.form.users = data;
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