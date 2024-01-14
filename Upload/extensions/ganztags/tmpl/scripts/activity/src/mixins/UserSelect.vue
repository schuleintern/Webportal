<template>

  <div class="si-userselect">

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <div v-if="!state">
      <button v-if="selected.length == 0" class="si-btn si-btn-green" v-on:click="handlerOpenForm"><i class="fas fa-plus"></i> Benutzer hinzufügen</button>
      <button v-else class="si-btn" v-on:click="handlerOpenForm"><i class="fas fa-plus"></i> Benutzerliste bearbeiten</button>
    </div>

    <div v-if="state == 'form'">
      <div class="si-userselect-modal" v-on:click.self="handlerCloseForm">
        <div class="si-userselect-modal-box" >
          <div class="si-userselect-modal-content">

            <div class="list">
              <ul class="userlist">
                <span v-if="users">
                  <li v-bind:key="index" v-for="(item, index) in  users" v-on:click="handlerSelectUser(item)"
                    :class="{'text-green': isSelected(item)}"  >
                    <span class="flex-1 flex-row">
                      <div class="flex-1"><img :src="item.avatar" class="width-3rem height-3rem"/></div>
                      <div class="flex-1 ">{{item.vorname}}</div>
                      <div class="flex-1 text-bold ">{{item.nachname}}</div>
                      <div class="flex-1 text-bold ">{{item.klasse}}</div>
                      <div class="flex-1 ">
                        <button v-if="item.type == 'isPupil'"
                                class="si-btn si-btn-off margin-r-m">Schüler</button>
                        <button v-if="item.type == 'isEltern'"
                                class="si-btn si-btn-off margin-r-m">Eltern</button>
                        <button v-if="item.type == 'isNone'"
                                class="si-btn si-btn-off margin-r-m">Sonstige</button>
                        <button v-if="item.type == 'isTeacher'"
                                class="si-btn si-btn-off margin-r-m">Lehrer</button>
                      </div>
                    </span>
                  </li>
                </span>
                <span v-else>Suche</span>

              </ul>
              <div class="padding-t-m flex-row" v-if="users.length">
                <div class="si-btn si-btn-off" ><i class="fa fa-user"></i> {{users.length}}</div>
                <div class="flex-1 flex-row flex-end">
                  <button class="si-btn" v-on:click="handlerSelectAll()">Alle rüber -></button>
                </div>

              </div>
            </div>

            <div class="form">
              <div class="si-form">
                <ul>
                  <li :class="{'text-red': required == true  }">
                    <label>Suche</label>
                    <input type="text" v-model="searchString" v-on:keyup="handlerChange" />
                  </li>
                  <li>
                    <label>Filter</label>
                    <div class="si-btn-multiple">
                      <button class="si-btn si-btn-light" :class="{'si-btn-active': filterType == ''}"
                              v-on:click="handlerFilterType('')">Alle</button>
                      <button class="si-btn si-btn-light" :class="{'si-btn-active': filterType == 'isTeacher'}"
                              v-on:click="handlerFilterType('isTeacher')" >Lehrer</button>
                      <button class="si-btn si-btn-light" :class="{'si-btn-active': filterType == 'isPupil'}"
                              v-on:click="handlerFilterType('isPupil')">Schüler</button>
                      <button class="si-btn si-btn-light" :class="{'si-btn-active': filterType == 'isEltern'}"
                              v-on:click="handlerFilterType('isEltern')">Eltern</button>
                      <button class="si-btn si-btn-light" :class="{'si-btn-active': filterType == 'isNone'}"
                              v-on:click="handlerFilterType('isNone')">Sonstige</button>
                    </div>
                  </li>
                  <li v-if="selected">
                    <label>Auswahl:</label>
                    <div class="text-right text-small">{{selected.length}}</div>
                    <div v-if="selected.length > 0" >
                      <ul class="selected">
                        <li v-bind:key="index" v-for="(item, index) in  selected" v-on:click="handlerSelectUser(item)">
                          <span class="flex-1 flex-row">
                            <div class="flex-1"><img :src="item.avatar" class="width-3rem height-3rem"/></div>
                            <div class="flex-1 vorname">{{item.vorname}}</div>
                            <div class="flex-1 text-bold nachname">{{item.nachname}}</div>
                            <div class="flex-1 ">
                              <button v-if="item.type == 'isPupil'"
                                      class="si-btn si-btn-off margin-r-m">Schüler</button>
                              <button v-if="item.type == 'isEltern'"
                                      class="si-btn si-btn-off margin-r-m">Eltern</button>
                              <button v-if="item.type == 'isNone'"
                                      class="si-btn si-btn-off margin-r-m">Sonstige</button>
                              <button v-if="item.type == 'isTeacher'"
                                      class="si-btn si-btn-off margin-r-m">Lehrer</button>
                            </div>
                          </span>
                        </li>
                      </ul>
                      <div></div>
                    </div>
                    <span v-else><label>-</label></span>
                  </li>
                  <li>
                    <button class="si-btn" v-on:click="handlerSubmit"><i class="fas fa-plus"></i> OK</button>
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

import Error from './Error.vue'
import Spinner from './Spinner.vue'

export default {
  components: {
    Error, Spinner
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

      users: false
    };
  },
  props: {
    preselected: Array
  },
  mounted: function () {
    this.selected = this.preselected;
  },
  created: function () {

  },
  methods: {

    handlerSelectAll: function () {
      this.users.forEach((item)  => {
        this.handlerSelectUser(item);
      });
    },
    handlerFilterType: function ( type ) {
      this.filterType = type;
      this.handlerChange();
    },

    isSelected: function (item) {
      if (!item.id) {
        return false;
      }
      for (var i = 0; i < this.selected.length; i++) {
        if ( parseInt(this.selected[i].id) == parseInt(item.id)) {
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

      this.loading = true;
      var that = this;
      axios.get( 'rest.php/GetUser/'+this.searchString+'/'+this.filterType)
      .then(function(response){
        if ( response.data ) {
          if (!response.data.error) {
            //console.log(response.data);
            if (response.data && response.data.length > 0) {
              that.users = response.data;
            } else {
              that.users = [];
            }

          } else {
            that.error = ''+response.data.msg;
            that.users = [];
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
          that.users = [];
        }
      })
      .catch(function(){
        that.error = 'Fehler beim Laden. 02';
        that.users = [];
      })
      .finally(function () {
        // always executed
        that.loading = false;
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

<style scoped>

</style>