<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <div class="si-details">
      <h3 class="margin-l-l" v-if="today">Deine Übersicht vom {{ today }}</h3>
      <ul>
        <li v-bind:key="index" v-for="(item, index) in  list" class="">


          <div class="text-big-m flex-row">
            <div v-if="item.color" class="margin-r-l"
                 :style="'width: 3rem; height: 3rem; border-radius: 3rem; background-color:'+item.color"></div>
            {{ item.title }}
          </div>

          <div class="margin-l-l margin-t-m padding-l-l">
            <div v-if="item.room">
              <label class="width-10rem">Raum:</label>
              {{ item.room }}
            </div>
            <div v-if="item.info">
              <label class="width-10rem">Info:</label>
              {{ item.info }}
            </div>
            <div v-if="item.leader && item.leader.userName">
              <label class="width-10rem">Leitung:</label>
              {{ item.leader.userName }}
            </div>
            <div v-if="item.schueler" class="width-55vw">
              <label class="width-10rem">Schüler*innen:</label>
              <table class="si-table si-table-style-allLeft si-table-small">
                <thead>
                <tr>
                  <td></td>
                  <td>Vorname</td>
                  <td>Nachname</td>
                  <td>Klasse</td>
                  <td>Info</td>
                  <td>Absenz</td>
                </tr>
                </thead>
                <tbody>
                <tr v-bind:key="key" v-for="(schueler, key) in  item.schueler" >
                  <td class="text-grey">{{key+1}}</td>
                  <td :class="{'text-line': schueler.absenz}">{{ schueler.vorname }}</td>
                  <td :class="{'text-line': schueler.absenz}">{{ schueler.nachname }}</td>
                  <td :class="{'text-line': schueler.absenz}">{{ schueler.klasse }}</td>
                  <td :class="{'text-line': schueler.absenz}">{{ schueler.info }}</td>
                  <td><span v-if="schueler.absenz" v-html="schueler.absenz"></span></td>
                </tr>
                </tbody>
              </table>

            </div>
          </div>



        </li>
      </ul>
    </div>


  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'


const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    AjaxError, AjaxSpinner
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      acl: window.globals.acl,
      error: false,
      loading: false,
      page: 'list',

      list: false,
      item: []

    };
  },
  computed: {
    today: function () {
      if (this.list && this.list[0] && this.list[0].date) {
        return this.list[0].date;
      }
      return false;
    }
  },
  created() {
    this.loadList();


    this.$bus.$on('page--open', data => {
      if (data.item) {
        this.item = data.item;
      } else {
        this.item = {
          id: 0,
          title: ''
        };
      }
      this.handlerPage(data.page);
    });


    this.$bus.$on('item--submit', data => {

      if (!data.item.title) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('title', data.item.title);
      formData.append('state', data.item.state);
      formData.append('color', data.item.color);
      formData.append('sort', data.item.sort);
      formData.append('preSelect', data.item.preSelect);
      formData.append('acl', data.item.acl);
      formData.append('ferien', data.item.ferien);
      formData.append('public', data.item.public);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setAdminKalender', formData)
          .then(function (response) {
            if (response.data) {

              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {

                that.loadList();
                if (data.callback) {
                  data.callback(response.data);
                }

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
          });


    });


    this.$bus.$on('item--delete', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/deleteAdminKalender', formData)
          .then(function (response) {
            if (response.data) {

              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.loadList();
                that.handlerPage();
              }
            } else {
              that.error = 'Fehler beim Speichern. 01';
            }
          })
          .catch(function (e) {
            console.log(e);
            that.error = 'Fehler beim Speichern. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });

    });


  },
  methods: {

    loadList() {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getMy')
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

    handlerPage(page = 'list') {
      this.page = page;
    },

  }
}
</script>

<style>

</style>
