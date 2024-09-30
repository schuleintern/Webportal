<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
        </button>
        <button class="si-btn" @click="handlerSubmit"><i class="fa fa-save"></i> Speichern</button>
      </div>
      <div>
        <button v-if="form.id" class="si-btn si-btn-red" v-on:click="handlerDelete"><i class="fas fa-trash"></i> Löschen</button>
      </div>
    </div>

    <div class="width-70vw">
      <div class="si-form ">
        <ul class="">
          <li>
            <label>Title</label>
            <input type="text" v-model="form.title" >
          </li>
          <li>
            <label>Status</label>
            <FormToggle  :input="form.state" @change="handlerToggleState"></FormToggle>
          </li>
          <li>
            <label>Anfang</label>
            <div class="flex-row">
              <input type="text" class="width-10rem" v-model="start_a" @change="handlerStart" placeholder="00" >
              <div class="flex-row flex-center-center padding-r-s padding-l-s"> : </div>
              <input type="text" class="width-10rem" v-model="start_b" @change="handlerStart" placeholder="00" >
            </div>
          </li>
          <li>
            <label>Ende</label>
            <div class="flex-row">
              <input type="text" class="width-10rem" v-model="end_a" @change="handlerEnd" placeholder="00" >
              <div class="flex-row flex-center-center padding-r-s padding-l-s"> : </div>
              <input type="text" class="width-10rem" v-model="end_b" @change="handlerEnd" placeholder="00" >
            </div>
          </li>



        </ul>

      </div>
    </div>

  </div>

</template>

<script>

import FormToggle from '../mixins/FormToggle.vue'

export default {
  name: 'ItemComponent',
  components: {
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

    handlerEnd: function () {
      this.form.end = this.end_a+':'+this.end_b;
    },
    handlerStart: function () {
      this.form.start = this.start_a+':'+this.start_b;
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