<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
      <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
      <button class="si-btn " @click="handlerSaveForm()"><i class="fa fa fa-save"></i> Speichern</button>
    </div>
    <div class="">
      <button class="si-btn si-btn-light" @click="handlerDelete()" v-if="deleteBtn === false"><i class="fa fa fa-trash"></i> Löschen</button>
      <button class="si-btn si-btn-red" @click="handlerDeleteDo(item)" v-if="deleteBtn === true"><i class="fa fa fa-trash"></i> Wirklich Löschen ?</button>
    </div> 
  </div>

    {{form}}
    <input type="text" v-model="form.folder">

    <div class="si-form" v-if="form">
      <ul>
        <li :class="required" class="flex-row">
          <div class="flex-5 flex margin-r-l">
            <label>Titel</label>
            <input type="text" v-model="form.title">

          </div>
          <div class="flex-1 flex">
            <label>Status</label>
          <FormToggle :input="form.state" @change="handlerState"></FormToggle>
          </div>
        </li>
        <li>
          <UserSelect @submit="handelerUserlist" :preselected="form.userlist"></UserSelect>
          <div v-if="form.userlist" class="padding-t-s">
            <span v-bind:key="index" v-for="(item, index) in  form.userlist" class="margin-b-s margin-r-s blockInline">
              <User v-bind:data="item"></User>
            </span>
          </div>

        </li>
      </ul>
    </div>
    <div class="si-form">  
      <ul>
        <li>
          {{form.folder}}

          <FormUpload v-if="form.folder"  @done="handerUpload" :target="'rest.php/fileshare/setUpload/'+form.folder" ></FormUpload>

        </li>
        <li v-bind:key="index" v-for="(item, index) in  form.childs"  class="flex-row">
          {{ item }}
          <input type="text" v-model="item.title" />

        </li>
      </ul>

    </div>

  </div>

</template>

<script>

import FormUpload from '../mixins/FormUpload.vue'
//import FormUpload2 from '../mixins/FormUpload2.vue'
import FormToggle from '../mixins/FormToggle.vue'
import UserSelect from '../mixins/UserSelect.vue'
import User from '../mixins/User.vue'

export default {
  name: 'ItemComponent',
  components: {
    FormUpload, FormToggle, UserSelect, User
  },
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false

      //,folder: window.globals.folder
    };
  },
  props: {
    acl: Array,
    item: []
  },
  created: function () {


    this.form = this.item;

    if ( this.form.id == 0 ) {
      this.form.folder = window.globals.randFolder;
    }


  },
  watch: {
    item: function(newVal)  {
      this.form = newVal;

      //console.log('wwww')
    }
  },
  methods: {

    handerUpload() {

      //console.log('complete', data)

    },
    handelerUserlist(data) {
      this.form.userlist = data;
    },

    handlerState(data) {
      this.form.state = data.value; 
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