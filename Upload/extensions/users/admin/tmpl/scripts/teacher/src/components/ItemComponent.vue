<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
        </button>
        <button class="si-btn" @click="handlerSubmit"><i class="fa fa-save"></i> Speichern</button>

      </div>
    </div>

    <div class="width-70vw">
      <div class="si-form ">
        <ul class="">
          <li>
            <label>User ID</label>
            <input type="text" v-model="form.userid" readonly >
          </li>
          <li>
            <label>ASV ID</label>
            <input type="text" v-model="form.asvid" >
          </li>
          <li>
            <label>Vorname</label>
            <input type="text" v-model="form.vorname" >
          </li>
          <li>
            <label>Rufname</label>
            <input type="text" v-model="form.rufname" >
          </li>
          <li>
            <label>Nachname</label>
            <input type="text" v-model="form.nachname" >
          </li>
          <li>
            <label>Kürzel</label>
            <input type="text" v-model="form.short" >
          </li>
          <li>
            <label>Geschlecht</label>
            <FormGender @change="handlerGender" :input="form.gender"></FormGender>
          </li>
          <li>
            <label>Zeugnissunterschrift</label>
            <input type="text" v-model="form.zeugniss" >
          </li>
          <li>
            <label>Amtsbezeichnung</label>
            <input type="number" v-model="form.amtbez" >
          </li>

        </ul>

      </div>
    </div>

  </div>

</template>

<script>

import FormGender from '../mixins/FormGender.vue'

export default {
  name: 'ItemComponent',
  components: {
   FormGender
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
    //this.form = this.item;
  },
  watch: {
    item: {
      immediate: true,
      handler (newVal) {

        this.form.asvid = newVal.lehrerAsvID;
        this.form.amtbez = newVal.lehrerAmtsbezeichnung;
        this.form.gender = newVal.lehrerGeschlecht;
        this.form.short = newVal.lehrerKuerzel;
        this.form.id = newVal.lehrerID;
        this.form.nachname = newVal.lehrerName;
        this.form.rufname = newVal.lehrerRufname;
        this.form.vorname = newVal.lehrerVornamen;
        this.form.userid = newVal.lehrerUserID;
        this.form.zeugniss = newVal.lehrerZeugnisunterschrift;
      }
    }
  },
  methods: {

    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },
    handlerSubmit: function () {
      this.$bus.$emit('item--submit', this.form);
    },
    handlerGender: function (value) {
      this.form.gender = value.value;
    }


  }


};
</script>

<style>

</style>