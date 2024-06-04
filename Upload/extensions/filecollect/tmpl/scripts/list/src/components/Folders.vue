<template>

  <div class="" v-if="acl.write == 1">

    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>
    <AjaxNotif v-bind:loading="loading"></AjaxNotif>

    <div v-if="acl.write == 1">

      <div class="si-form" v-bind:key="index" v-for="(child, index) in items">

        <FolderForm v-bind:acl="acl" v-bind:item="child" v-bind:index="index" v-bind:root="item"></FolderForm>


        <div v-show="child.files.length > 0" class="padding-l">
          <div class="flex-row">
            <div class="flex-1">
              <h4>Daten:</h4>
            </div>
            <div class="flex-1 text-right">
              <a class="si-btn si-btn-green" :href="'index.php?page=ext_filecollect&view=list&task=download&foid='+child.id" target="_blank"><i class="fa fa-download"></i> Download </a>
            </div>
          </div>


          <table class="si-table si-table-style-firstLeft">
            <thead>
            <tr>
              <td>Dateiname</td>
              <td>Benutzer*in</td>
              <td>Datum</td>
              <td></td>
            </tr>
            </thead>
            <tbody>
            <tr v-bind:key="i" v-for="(file, i) in  child.files" class="">
              <td class="flex-1"><i class="fa fa-file margin-r-s"></i> <a
                  :href="'index.php?page=ext_filecollect&view=list&task=open&fiid='+file.fileid"
                  target="_blank">{{ file.filename }}</a></td>
              <td class=" flex-1 text-small">{{ file.user.name }}</td>
              <td class=" flex-1 text-right text-small">{{ file.time }}</td>
              <td>
                <button class="si-btn si-btn-icon si-btn-light" v-on:click="handlerRemoveFile(file)"><i
                    class="fa fa-trash"></i></button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

      </div>

      <button class="si-btn si-btn-border width-55vw height-15rem text-big-m" v-on:click="handlerAddFolder"><i class="fa fa-plus"></i> Ordner Hinzufügen</button>
      <br><br>
      <button class="si-btn" v-on:click="handlerChange"><i class="fa fa-save"></i> Speichern</button>

    </div>


  </div>

</template>


<script>

import FolderForm from './FolderForm.vue'
import {default as axios} from "axios";
import AjaxError from '../mixins/AjaxError.vue'
import AjaxSpinner from '../mixins/AjaxSpinner.vue'
import AjaxNotif from '../mixins/AjaxNotif.vue'

export default {
  components: {
    FolderForm, AjaxError, AjaxSpinner, AjaxNotif
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

    var that = this;
    this.$bus.$on('folder--remove', data => {
      that.handlerRemove(data.index);
    });

    this.$bus.$on('folder--submit', () => {
      that.handlerChange();
    });


  },
  mounted() {

  },

  methods: {

    handlerRemoveFile: function (item) {


      if (confirm("Datei wirklich löschen?") == true) {
        console.log(item.id);

        if (!item.id) {
          console.log('missing');
          return false;
        }

        const formData = new FormData();
        formData.append('fiid', item.id);

        this.loading = true;
        var that = this;
        axios.post(window.globals.apiURL + '/deleteFile', formData)
            .then(function (response) {
              if (response.data) {

                if (response.data.error) {
                  that.error = '' + response.data.msg;
                }
                that.loadItems();

              } else {
                that.error = 'Fehler beim Löschen. 01';
              }
            })
            .catch(function () {
              that.error = 'Fehler beim Löschen. 02';
            })
            .finally(function () {
              // always executed
              that.loading = false;
              //that.loadItems();
            });


      }

    },
    handlerRemove: function (index) {
      if (this.items[index].files.length > 0) {
        this.error = 'Der Ordner enthält noch Daten.'
      } else {
        this.items.splice(index, 1);
      }

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
              that.$bus.$emit('notif--open', {
                msg: 'Gespeichert'
              });

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
      this.items.push({
        "id": "",
        "title": "",
        "anzahl": 1,
        "status": 1,
        "members": this.item.members,
        "endDate": this.item.endDate,
        "files": []
      });
    },
    loadItems() {

      if (!this.item.id) {
        return false;
      }
      this.loading = true;
      var that = this;
      axios.get(window.globals.apiURL + '/getFolders/' + this.item.id)
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
