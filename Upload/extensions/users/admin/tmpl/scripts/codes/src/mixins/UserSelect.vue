<template>
  <div class="si-userselect">
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <div v-if="!state">
      <button v-if="selected.length == 0" class="si-btn si-btn-green" v-on:click="handlerOpenForm"><i
          class="fas fa-plus"></i> Benutzer hinzufügen</button>
      <button v-else class="si-btn" v-on:click="handlerOpenForm"><i class="fas fa-plus"></i> Benutzerliste
        bearbeiten</button>
    </div>

    <div v-if="state == 'form'">
      <div class="si-userselect-modal" v-on:click.self="handlerCloseForm">
        <div class="si-userselect-modal-box">
          <div class="si-userselect-modal-content">

            <div class="list">
              <ul class="userlist">
                <span v-if="users">
                  <li v-bind:key="index" v-for="(item, index) in  users" v-on:click="handlerSelectUser(item)"
                      :class="{ 'text-green': isSelected(item) }">
                    <span class="flex-1 flex-row">
                      <div class="flex-1"><img :src="item.avatar" class="width-3rem height-3rem" /></div>
                      <div class="flex-1 ">{{ item.vorname }}</div>
                      <div class="flex-1 text-bold ">{{ item.nachname }}</div>
                      <div class="flex-1 text-bold ">{{ item.klasse }}</div>
                      <div class="flex-1 ">
                        <button v-if="item.type == 'isPupil'" class="si-btn si-btn-off margin-r-m">Schüler*in</button>
                        <button v-if="item.type == 'isEltern'" class="si-btn si-btn-off margin-r-m">Eltern</button>
                        <button v-if="item.type == 'isNone'" class="si-btn si-btn-off margin-r-m">Sonstige</button>
                        <button v-if="item.type == 'isTeacher'" class="si-btn si-btn-off margin-r-m">Lehrer*in</button>
                      </div>
                    </span>
                  </li>
                </span>
                <span v-else>

                </span>

              </ul>
              <div class="padding-t-m flex-row" v-if="users.length">
                <div class="si-btn si-btn-off"><i class="fa fa-user"></i> {{ users.length }}</div>
                <div class="flex-1 flex-row flex-end">
                  <button class="si-btn si-btn-icon" v-on:click="handlerSelectAll()">Alle <i
                      class="fas fa-arrow-right"></i></button>
                </div>

              </div>
            </div>

            <div class="form">
              <div class="si-form">
                <ul>
                  <li :class="{ 'text-red': required == true }">
                    <label>Suche</label>
                    <input type="text" v-model="searchString" v-on:keyup="handlerChange" />
                  </li>
                  <li>
                    <label>Filter</label>
                    <div class="si-btn-multiple">
                      <button class="si-btn si-btn-small si-btn-light" :class="{ 'si-btn-active': filterType == '' }"
                              v-on:click="handlerFilterType('')">Alle</button>
                      <button class="si-btn si-btn-small si-btn-light"
                              :class="{ 'si-btn-active': filterType == 'isTeacher' }"
                              v-on:click="handlerFilterType('isTeacher')">Lehrer*in</button>
                      <button class="si-btn si-btn-small si-btn-light" :class="{ 'si-btn-active': filterType == 'isPupil' }"
                              v-on:click="handlerFilterType('isPupil')">Schüler*in</button>
                      <button class="si-btn si-btn-small si-btn-light"
                              :class="{ 'si-btn-active': filterType == 'isEltern' }"
                              v-on:click="handlerFilterType('isEltern')">Eltern</button>
                      <button class="si-btn si-btn-small si-btn-light" :class="{ 'si-btn-active': filterType == 'isNone' }"
                              v-on:click="handlerFilterType('isNone')">Sonstige</button>
                    </div>
                  </li>
                  <li v-if="selected.length > 0">
                    <div class="flex-row">
                      <label class="flex-1">Auswahl:</label>
                      <div class="text-right text-small">{{ selected.length }}</div>
                    </div>


                    <div v-if="selected.length > 0" class="selected">
                      <table class="si-table">
                        <tr v-bind:key="index" v-for="(item, index) in  selected" class="padding-0"
                            style="padding-top: 0.3rem; padding-bottom: 0.3rem">

                          <td class="1 "><img :src="item.avatar" class="width-3rem height-3rem" /></td>
                          <td class="padding-l-s vorname ">{{ item.vorname }}</td>
                          <td class=" text-bold nachname ">{{ item.nachname }}</td>
                          <td class=" ">
                            <button v-if="item.type == 'isPupil'" class="si-btn si-btn-off margin-r-m">Schüler*in</button>
                            <button v-if="item.type == 'isEltern'" class="si-btn si-btn-off margin-r-m">Eltern</button>
                            <button v-if="item.type == 'isNone'" class="si-btn si-btn-off margin-r-m">Sonstige</button>
                            <button v-if="item.type == 'isTeacher'"
                                    class="si-btn si-btn-off margin-r-m">Lehrer*in</button>
                          </td>
                          <td class=" ">
                            <button class="si-btn si-btn-icon" v-on:click="handlerSelectUser(item)"><i
                                class="fa fa-trash"></i></button>
                          </td>

                        </tr>
                      </table>
                    </div>
                    <span v-else><label>-</label></span>
                  </li>
                  <li class="flex-row">
                    <button v-if="showSubmit() == true" class="si-btn si-btn-green margin-r-m flex-1"
                            v-on:click="handlerSubmit"><i class="fas fa-plus"></i> OK</button>
                    <button class="si-btn" v-on:click="handlerCloseForm"><i class="fa fa-times-circle"></i>
                      Abbrechen</button>
                  </li>
                </ul>

              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>

const axios = require('axios').default;

import AjaxError from './AjaxError.vue'
import AjaxSpinner from './AjaxSpinner.vue'

export default {
  components: {
    AjaxError, AjaxSpinner
  },
  data() {
    return {
      loading: false,
      error: false,

      required: false,

      selected: [],

      state: false,
      searchString: '',

      filterType: '',

      users: false,
      ajaxRequest: false


    };
  },
  props: {
    prefilter: String,
    preselected: Array,
    minAnzahl: Number,
    maxAnzahl: Number
  },
  watch: {
    preselected: function (newVal) {
      this.selected = newVal;
    }
  },
  mounted: function () {
    if (this.preselected[0] != false) {
      this.selected = this.preselected;
    }
    if (!this.selected) {
      this.selected = [];
    }

    if (this.prefilter) {
      this.filterType = this.prefilter;
    }


  },
  created: function () {

  },
  methods: {

    showSubmit: function () {

      if (this.minAnzahl && !this.maxAnzahl) {
        if (this.minAnzahl >= this.selected.length) {
          return true;
        }
      }
      if (this.maxAnzahl) {
        if (this.selected.length <= this.maxAnzahl) {
          return true;
        }
      }
      if (!this.minAnzahl && !this.maxAnzahl) {
        return true;
      }
      return false;
    },
    handlerSelectAll: function () {
      this.users.forEach((item) => {
        this.handlerSelectUser(item);
      });
    },
    handlerFilterType: function (type) {
      this.filterType = type;
      this.handlerChange();
    },

    isSelected: function (item) {
      if (!item.id) {
        return false;
      }
      for (var i = 0; i < this.selected.length; i++) {
        if (parseInt(this.selected[i].id) == parseInt(item.id)) {
          //console.log('---jo',this.selected[i].id,item.id)
          return true;
        }
      }
      return false;
    },
    handlerSubmit: function () {
      this.$emit('submit', this.selected)
      this.handlerCloseForm();
    },
    handlerSelectUser: function (user) {
      let found = false;
      this.selected.forEach((select, i) => {
        if (select.id == user.id) {
          found = true;
          this.selected.splice(i, 1);
        }
      })
      if (!found) {
        this.selected.push(user);
      }

    },
    handlerChange: function () {

      this.required = false;
      if (this.searchString == '') {
        this.required = true;
        return false;
      }


      if (this.ajaxRequest) {
        this.ajaxRequest.cancel();
      }
      this.ajaxRequest = axios.CancelToken.source();

      this.loading = true;
      var that = this;
      axios.get('rest.php/GetUser/' + this.searchString + '/' + this.filterType, {cancelToken: this.ajaxRequest.token})
          .then(function (response) {
            if (response.data) {
              if (!response.data.error) {

                //console.log(response.data);
                if (response.data && response.data.length > 0) {
                  that.users = response.data;
                } else {
                  that.users = [];
                }

              } else {
                that.error = '' + response.data.msg;
                that.users = [];
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
              that.users = [];
            }
            that.loading = false;
          })
          .catch(function (err) {
            if (axios.isCancel(err)) {
              //console.log('Previous request canceled, new request is send', err.message);
              //that.loading = false;
            } else {
              // handle error
              that.error = 'Fehler beim Laden. 02';
              that.users = [];
              that.loading = false;
            }

          })
          .finally(function () {
            // always executed
            //that.loading = false;
          });

    },
    handlerCloseForm: function () {
      this.state = false;
    },
    handlerOpenForm: function () {
      this.state = 'form';
    }

  }

};
</script>

<style scoped></style>