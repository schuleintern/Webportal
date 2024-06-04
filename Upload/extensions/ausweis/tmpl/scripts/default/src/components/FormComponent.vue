<template>
  <div class="">
    <AjaxError v-bind:error="error"></AjaxError>

    <div class="flex-row flex-space-between">
      <div class="flex-1">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i>
          Zurück</button>
        <button class="si-btn si-btn-green" @click="handlerSaveForm()"><i class="fa fa-check"></i> Beantragen</button>
      </div>
      <div class="flex-1 flex-row flex-end">

      </div>
    </div>

    <form class="si-form">

      <ul class="">
        <li :class="required">
          <label>Name</label>
          <select v-model="form.user_id">
            <option v-bind:key="index" v-for="(item, index) in  users" :value="item.id">{{ item.name }}</option>
          </select>
        </li>

        <li v-if="form.file && form.file.path">
          <label>Foto</label>
          
          <cropper :src="form.file.path" @change="changeCropper" :stencil-props="{
            aspectRatio: 3 / 4,
            movable: true,
            resizable: true
          }" />
        </li>
        <li v-else :class="required">
          <label>Foto</label>
          
          <input type="hidden" v-model="form.image" required>
          <div v-if="progressBar > 0">Upload: {{ progressBar }}%</div>

          <FileUpload :target="apiURL + '/setUploadImg'" action="POST" v-on:progress="progress" v-on:start="startUpload"
            v-on:finish="finishUpload"></FileUpload>

            <div class="text-small padding-r-l">Dateiformat: jpg oder png</div>
        </li>
      </ul>



    </form>

  </div>
</template>

<script>

import AjaxError from '../mixins/AjaxError.vue'
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';

import FileUpload from 'vue-simple-upload/dist/FileUpload'

export default {
  name: 'ItemComponent',
  components: {
    FileUpload, Cropper, AjaxError
  },
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false,
      progressBar: 0,
      error: false
    };
  },
  setup() {

    const format = (val) => {
      return `${val.getDate()}.${val.getMonth() + 1}.${val.getFullYear()}`
    }

    return {
      format
    }
  },
  props: {
    acl: Array,
    item: [],
    apiURL: String,
    users: Object
  },
  created: function () {
    //this.form = this.item;

    if (this.users[0]) {
      this.form.user_id = this.users[0].id;
    }


  },
  methods: {
    changeCropper({ coordinates, canvas }) {
      //console.log(coordinates, canvas);
      this.coordinates = coordinates;
      // You able to do different manipulations at a canvas
      // but there we just get a cropped image, that can be used 
      // as src for <img/> to preview result
      this.form.image = canvas.toDataURL();

    },
    startUpload() {
      // file upload start event
      //console.log(e);
    },
    finishUpload(e) {
      let obj = JSON.parse(e.target.responseText);
      if (obj.error && obj.msg) {
        this.error = obj.msg;
      } else {
        this.form.file = obj;
      }
      //this.handlerSaveForm();

    },
    progress(e) {
      this.progressBar = e;
    },
    handelerUsers: function (data) {
      this.form.users = data;
    },
    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'item'
      });
    },

    handlerSaveForm() {

      if (!this.form.user_id) {
        this.required = 'required';
        return false;
      }
      if (!this.form.image) {
        this.required = 'required';
        return false;
      }

      var that = this;
      this.$bus.$emit('item--submit', {
        item: this.form,
        callback: function () {
          //that.item.id = data.id;
          that.required = '';
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

<style></style>