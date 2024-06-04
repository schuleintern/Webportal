<template>

  <div class="" v-if="acl.write == 1">

    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    FOLDERS!!
    <br><br>


    {{ item }}

    <br><br>


    <br>

    FROM API - ITEMS:
    {{ items }}

    <br>

    <div v-if="acl.write == 1">

        <div class="si-form" v-bind:key="index" v-for="(child, index) in items">

          <FolderForm v-bind:acl="acl" v-bind:item="child" v-bind:root="item"></FolderForm>

          ------------
          <br>
          DATEN:
          <br><br>

          <button class="si-btn si-btn-light" v-on:click="handlerRemove(index)"><i class="fa fas fa-trash"></i> Entfernen</button>

        </div>

      <button class="si-btn" v-on:click="handlerAddFolder"><i class="fa fa-plus"></i> Ordner Hinzufügen</button>
      <button class="si-btn" v-on:click="handlerChange"><i class="fa fa-save"></i> Speichern</button>

    </div>


  </div>

</template>


<script>

import FolderForm from './FolderForm.vue'
import {default as axios} from "axios";
import AjaxError from '../mixins/AjaxError.vue'
import AjaxSpinner from '../mixins/AjaxSpinner.vue'

export default {
  components: {
    FolderForm, AjaxError, AjaxSpinner
  },
  name: 'Item',
  props: {
    item: Object,
    acl: Object
  },
  data() {
    return {
      error: false,
      loading: false,

      items: []
    }
  },
  computed: {},
  created: function () {
    this.loadItems();
  },
  mounted() {

  },

  methods: {
    handlerRemove: function (index) {
      //console.log(this.items, index);
      this.items.splice(index, 1);
    },
    handlerChange: function () {

      if (this.items.length < 1) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('items', JSON.stringify(this.items));

      this.loading = true;
      var that = this;
      axios.post(window.globals.apiURL + '/setFolders', formData)
          .then(function (response) {
            if (response.data) {

              if (response.data.error) {
                that.error = '' + response.data.msg;
              }

            } else {
              that.error = 'Fehler beim Speichern. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Speichern. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
            //that.loadItems();
          });


    },
    handlerAddFolder() {
      this.items.push({"id": "", "title": "", "anzahl": 1, "status": 1, "members": this.item.members, "endDate": this.item.endDate});
    },
    loadItems() {

      this.loading = true;
      var that = this;
      axios.get(window.globals.apiURL + '/getFolders')
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.items = response.data;
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Laden. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });

    },
  }
}
</script>
