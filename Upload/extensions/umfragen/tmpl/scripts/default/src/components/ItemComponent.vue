<template>
  <div class="">

    <div class="flex-row">

      <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>

      <button class="si-btn" @click="handlerSaveForm()"><i class="fa fa-save"></i> Speichern</button>

    </div> 



    <div class="">

      <h3>{{ form.title }}</h3>

      <div class="si-form">
        <UmfragenAnswer :form="form"></UmfragenAnswer>
      </div>


    </div>
  </div>

</template>

<script>

import UmfragenAnswer from '../mixins/UmfragenAnswer.vue'


export default {
  name: 'ItemComponent',
  components: {
    UmfragenAnswer
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

      let validate = true;
      this.form.childs.map((o) => {
        if (!o.value || o.value == 'undefined') {
          validate = false;
        }
      });

      if (validate == false) {
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