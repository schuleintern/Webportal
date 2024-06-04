<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
        </button>
        <button class="si-btn" @click="handlerAdd"><i class="fa fa-save"></i> Hinzufügen</button>

      </div>
    </div>

    <div class="width-70vw">
      <h2>{{ item.title }}</h2>

      <table class="si-table si-table-style-allLeft" v-if="list && list.length >= 1">
        <thead>
        <tr>
          <th>Bild</th>
          <th>Titel</th>
          <th>Status</th>
          <th>Datei</th>
          <th>Ablaufdatum</th>
          <th></th>
        </tr>
        </thead>
        <tbody>
        <tr v-bind:key="index" v-for="(child, index) in  list" class="">
          <td><img v-if="child.coverURL" :src="child.coverURL" width="100"></td>
          <td>{{ child.title }}</td>
          <td>
            <FormToggle :disable="false" :input="child.state"></FormToggle>
          </td>
          <td>
            <button v-if="child.pdf" class="si-btn si-btn-off si-btn-icon"><i class="fa fa-file-pdf"></i></button>
          </td>
          <td>{{ child.enddate }}</td>
          <td>
            <button class="si-btn si-btn-light margin-l-l" @click="handlerOpen(child)"><i class="fa fa-edit"></i>
              Bearbeiten
            </button>
          </td>
        </tr>
        </tbody>
      </table>
      <div v-else>
        <div class="padding-m"><i>- Noch keine Inhalte vorhanden -</i></div>
      </div>


    </div>

  </div>

</template>

<script>

import FormToggle from "@/mixins/FormToggle.vue";

const axios = require('axios').default;

export default {
  name: 'ItemComponent',
  components: {
    FormToggle

  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      list: false
    };
  },
  props: {
    acl: Array,
    item: []
  },
  created: function () {
    this.loadList();
  },
  methods: {
    handlerOpen(item) {

      this.$bus.$emit('page--editform', {
        page: 'editform',
        item: item
      });

    },
    handlerAdd: function () {
      this.$bus.$emit('page--editform', {
        page: 'editform',
        item: this.list
      });
    },
    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },
    loadList() {

      if (!this.item || !this.item.id) {
        return false;
      }
      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getItems/' + this.item.id)
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.list = response.data;
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


};
</script>

<style>

</style>