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
        <div class="flex-row">
          <div class="flex-3">
            <ul class="">
              <li>
                <label>Tag</label>
                <FormMulti :input="form.day" :options="optionsDay" @submit="handlerDay"></FormMulti>
              </li>
              <li>
                <label>Stunde</label>
                <FormMulti :input="form.hour" :options="optionsHour" @submit="handlerHour"></FormMulti>
              </li>
              <li class="flex-row">
                <div class="flex-1 margin-b-l">
                  <label>Lehrer*in</label>
                  <FormSelect :input="form.teacher" :options="teachers" @submit="handlerTeacher"></FormSelect>
                </div>
                <div class="flex-1 margin-b-l">
                  <label>Fach</label>
                  <FormSelect :input="form.fach" :options="fach" @submit="handlerFach"></FormSelect>
                </div>
                <div class="flex-1 margin-b-l">
                  <label>Klasse</label>
                  <FormSelect :input="form.klasse" :options="klassen" @submit="handlerKlassen"></FormSelect>
                </div>
                <div class="flex-1 margin-b-l">
                  <label>Raum</label>
                  <input type="text" v-model="form.room">
                </div>
              </li>
            </ul>
          </div>
          <div class="flex-1">
            <ul class="">

              <li>
                <label>Stundenplan ID</label>
                <input type="text" v-model="form.stundenplanID" readonly>
              </li>
            </ul>
          </div>
        </div>
        <ul class="">

        </ul>

      </div>
    </div>

  </div>

</template>

<script>

import FormSelect from './../mixins/FormSelect.vue'
import FormMulti from './../mixins/FormMulti.vue'

export default {
  name: 'ItemComponent',
  components: {
    FormSelect, FormMulti
  },
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false,
      stundenplanID: window.globals.id,
      anzStunden: window.globals.anzStunden,
      klassen: JSON.parse(window.globals.klassen),
      fach: JSON.parse(window.globals.fach),
      teachers: JSON.parse(window.globals.teacher),
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
    this.form.stundenplanID = this.stundenplanID;
  },
  computed: {
    optionsHour: function () {

      var ret = [];
      for (let i = 1; i <= this.anzStunden; i++) {
        ret.push({
          "title":i,
          "value":i
        });
      }
      return ret;

    }
  },
  methods: {
    handlerTeacher: function (val) {
      this.form.teacher = val.value;
    },
    handlerFach: function (val) {
      this.form.fach = val.value;
    },
    handlerKlassen: function (val) {
      this.form.klasse = val.value;
    },
    handlerHour: function (val) {
      this.form.hour = val.value;
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

      if (!this.form.stundenplanID) {
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