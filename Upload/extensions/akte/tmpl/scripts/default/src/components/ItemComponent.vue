<template>
  <div class="">

    <div class="flex-row">

      <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>

      <button class="si-btn" @click="handlerSaveForm()"><i class="fa fa-save"></i> Speichern</button>

    </div> 



    <div class="">

      <h3>{{ form.title }}</h3>
      
      <div class="si-form">  
        <ul>
          <li v-bind:key="index" v-for="(item, index) in  form.childs"  class="flex" :class="{'required': !item.value}">
            <h4>{{ index+1 }}. {{ item.title }}</h4>

            <div v-if="item.typ == 'text'" class="flex">
              <input type="text" v-model="item.value" >
            </div>

            <div v-if="item.typ == 'number'" class="flex">
              <input type="number" v-model="item.value">
            </div>

            <div v-if="item.typ == 'boolean'" class="flex-row flex-center-center">
              
              <button class="si-btn si-btn-toggle-off margin-r-m" v-on:click="handlerBoolean(1, item)" :class="{'si-btn-active': item.value == 1}">
                <i class="fa fas fa-toggle-on"></i> Ja</button>

              <button class="si-btn si-btn-toggle-off" v-on:click="handlerBoolean(2,item)"  :class="{'si-btn-active': item.value == 2}">
                <i class="fa fas fa-toggle-off"></i> Nein</button>

            </div>
            
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