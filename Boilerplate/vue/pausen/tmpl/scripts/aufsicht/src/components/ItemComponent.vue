<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
        </button>
        <button class="si-btn" v-on:click="handlerSubmit"><i class="fas fa-save"></i> Speichern</button>
      </div>
      <div>
        <button v-if="form.id" class="si-btn si-btn-red" v-on:click="handlerDelete"><i class="fas fa-trash"></i> Löschen</button>
      </div>
    </div>

    <div class="width-70vw">
      <div class="si-form ">
        <ul class="">
          <li>
            <label>Tag</label>
            <FormMulti :input="form.day" :options="optionsDay" @submit="handlerDay"></FormMulti>
          </li>
          <li>
            <label>Benutzer*in</label>
            <User v-if="form.user" :data="form.user[0]"></User>
            <UserSelect @submit="handelerUsers" :preselected="form.user" maxAnzahl="1"></UserSelect>
          </li>
          <li>
            <label>Pause</label>
            <FormMulti :input="form.pausen_id" :options="optionsPausen" @submit="handlerPausen"></FormMulti>
          </li>

        </ul>

      </div>
    </div>

  </div>

</template>

<script>

import User from '../mixins/User.vue'
import UserSelect from '../mixins/UserSelect.vue'
import FormMulti from '../mixins/FormMulti.vue'

export default {
  name: 'ItemComponent',
  components: {
    UserSelect,
    FormMulti,
   User
  },
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false,
      optionsPausen: JSON.parse(window.globals.optionsPausen),
      optionsDay: [
        {
          "title":"Montag",
          "value":"1"
        },
        {
          "title":"Dienstag",
          "value":"2"
        },
        {
          "title":"Mittwoch",
          "value":"3"
        },
        {
          "title":"Donnerstag",
          "value":"4"
        },
        {
          "title":"Freitag",
          "value":"5"
        }
      ]
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
    handelerUsers: function (data) {
      this.form.user = data;
    },
    handlerPausen: function (val) {
      this.form.pausen_id = val.value;
    },
    handlerDay: function (val) {
      this.form.day = val.value;
    },
    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },

    handlerSubmit: function () {

      if (!this.form.day) {
        return false;
      }
      this.deleteItem = false;
      this.$bus.$emit('item--submit', {
        item: this.form
      });


    },
    handlerDelete: function () {
      if (!this.form.id) {
        return false;
      }
      if (confirm('Wirklich löschen?')) {
        this.$bus.$emit('item--delete', {
          item: this.form
        });
      }

    }


  }


};
</script>

<style>

</style>