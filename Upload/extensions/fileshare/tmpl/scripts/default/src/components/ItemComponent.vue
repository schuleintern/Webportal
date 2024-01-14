<template>
  <div class="">

    <div class="flex-row">

      <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>

    </div> 



    <div class="">

      <h3>{{ form.title }}</h3>
      
      <div class="si-form">  
        <ul>
          <li v-bind:key="index" v-for="(item, index) in  form.childs"  class="flex">
           {{ item }}
          </li>
        </ul>
      </div>

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

    handlerBoolean(val,item) {
      item.value = val;
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