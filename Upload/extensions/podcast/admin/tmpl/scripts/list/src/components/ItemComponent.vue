<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <button class="si-btn si-btn-light" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
      <button class="si-btn si-btn-light" @click="handlerDelete()" v-if="deleteBtn === false"><i class="fa fa fa-trash"></i> Löschen</button>
      <button class="si-btn si-btn-red" @click="handlerDeleteDo(item)" v-if="deleteBtn === true"><i class="fa fa fa-trash"></i> Wirklich Löschen ?</button>
    </div>


    <div class="flex-row">
      <div class="flex-2 margin-r-m">
        <form class="si-form" @change="handlerSaveForm($event)">
          <ul>
            <li :class="required">
              <label>Titel</label>
              <input type="text" v-model="form.title">
            </li>
            <li class="">
              <label>Beschreibung</label>
              <textarea v-model="form.desc"></textarea>
            </li>
            <li class="">
              <label>Autor*innen</label>
              <input type="text" v-model="form.author">
            </li>
          </ul>
        </form>
      </div>
      <div class="flex-1 si-form">
        <ul class="">
          <li class="">
            <label>Bild</label>
            <input type="text" readonly v-model="form.cover" class="margin-b-s" />
            <FileUpload :target="'index.php?page=ext_podcast&view=list&admin=true&task=uploadCover&id='+form.id" action="POST"
                        v-on:finish="finishUploadCover"></FileUpload>
          </li>
          <li class="">
            <label>Audio-Datei (*.mp3)</label>
            <input type="text" readonly v-model="form.file" class="margin-b-s" />
            <div v-if="progressBar > 0">Upload: {{progressBar}}%</div>
            <FileUpload :target="'index.php?page=ext_podcast&view=list&admin=true&task=uploadAudio&id='+form.id" action="POST"
                        v-on:progress="progress" v-on:start="startUpload" v-on:finish="finishUpload"></FileUpload>

          </li>
        </ul>
      </div>
    </div>





  </div>

</template>


<script>

import FileUpload from 'vue-simple-upload/dist/FileUpload'

export default {
  name: 'ItemComponent',
  components: {
    FileUpload,
  },
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false,
      progressBar: 0
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

    startUpload() {
      // file upload start event
      //console.log(e);
    },
    finishUpload(e) {
      this.form.file = e.target.responseText;
      this.handlerSaveForm();

    },
    progress(e) {
      this.progressBar = e;
    },

    finishUploadCover(e) {
      this.form.cover = e.target.responseText;
      this.handlerSaveForm();

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

