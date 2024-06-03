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
            <FormToggle :input="form.state" @change="handlerToggleState"></FormToggle>
          </li>
          <li>
            <label>Kategorie</label>
            <FormMulti :input="form.cat_id" :options="categories" @submit="handlerCat"></FormMulti>
          </li>

        </ul>

      </div>
    </div>

  </div>

</template>

<script>

import FormToggle from '../mixins/FormToggle.vue'
import FormMulti from '../mixins/FormMulti.vue'

export default {
  name: 'ItemComponent',
  components: {
    FormToggle, FormMulti
  },
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false,
      categories: window.globals.categories

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
    handlerCat: function (data) {
      this.form.cat_id = data.value;
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