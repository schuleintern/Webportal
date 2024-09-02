<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="flex-1">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
        <button v-on:click="handlerSaveForm" class="si-btn"><i class="fa fa-save"></i> Speichern</button>
      </div>
      <div class="flex-1 flex-row flex-end">
        <button class="si-btn si-btn-light"  @click="handlerDelete()" v-if="form.id"><i class="fa fa fa-trash"></i> Löschen</button>
      </div>
    </div>

    <div class="si-form">
      <ul>
        <li :class="required">
          <label>Titel</label>
          <input type="text" v-model="form.title">
        </li>

      </ul>

    </div>

  </div>

</template>

<script>


export default {
  name: 'ItemComponent',
  components: {

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
      if (!this.form.id) {
        return false;
      }
      if (confirm('Wirklich löschen?')) {
        this.$bus.$emit('item--delete', {
          item: this.form
        });
      }
    },


  }


};
</script>

<style>

</style>