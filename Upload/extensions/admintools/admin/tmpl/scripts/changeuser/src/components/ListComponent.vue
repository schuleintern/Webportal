<template>
  <div class="">

    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <input type="search" class="si-input" v-model="searchString" placeholder="Suche..."/>

    <button class="margin-l-s si-btn si-btn-icon si-btn-light" @click="handlerKillSearch"><i class="fa fa-times"></i></button>

    <div v-if="sortList.length >= 1 && ajaxUsers == false">
      <table class="si-table si-table-style-allLeft" v-if="sortList && sortList.length >= 1">
        <thead>
        <tr>
          <th v-on:click="handlerSort('id')" class="curser-sort"></th>
          <th v-on:click="handlerSort('user_id')" class="curser-sort">Benutzer*in</th>
          <th >Klasse</th>
          <th >Type</th>
          <th ></th>
          <th></th>
        </tr>
        </thead>
        <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  sortList" class="">
          <td><img  :src="item.user.avatar" class="avatar width-3rem height-3rem"/></td>
          <td>{{ item.user.name }}</td>
          <td>{{ item.user.klasse }}</td>
          <td>{{ item.user.type }}</td>
          <td>
            <a class="si-btn margin-r-l"  :href="'index.php?page=ext_admintools&view=changeuser&admin=true&task=loginUser&uid='+item.user_id" ><i class="fa fa-lock"></i> Login</a>
            <button class="si-btn si-btn-icon si-btn-border" v-on:click="handlerRemove(item)"><i class="fa fa-trash"></i></button>

          </td>
        </tr>
        </tbody>
      </table>
      <div v-else>
        <div class="padding-m"><i>- Noch keine Inhalte vorhanden -</i></div>
      </div>
    </div>

    <div v-if="sortList.length < 1">
      <table class="si-table  si-table-style-allLeft" v-if="ajaxUsers && ajaxUsers.length >= 1">
        <thead>
        <tr>
          <th></th>
          <th>Benutzer*in</th>
          <th>Klasse</th>
          <th>Type</th>
          <th>ID</th>
          <th></th>
        </tr>
        </thead>
        <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  ajaxUsers" class="">
          <td><img  :src="item.avatar" class="avatar width-3rem height-3rem"/></td>
          <td>{{ item.name }}</td>
          <td>{{ item.klasse }}</td>
          <td>{{ item.type }}</td>
          <td>{{ item.id }}</td>
          <td>
            <a class="si-btn margin-r-s"  :href="'index.php?page=ext_admintools&view=changeuser&admin=true&task=loginUser&uid='+item.id" ><i class="fa fa-lock"></i> Login</a>
            <button class="si-btn si-btn-icon" v-on:click="handlerAdd(item)"><i class="fa fa-plus"></i></button>
          </td>
        </tr>
        </tbody>
      </table>
      <div v-else>
        <div class="padding-m" v-if="loading == false"><i>- Kein Benutzer gefunden -</i></div>
      </div>
    </div>


  </div>
</template>

<script>

import {default as axios} from "axios";
import AjaxError from '../mixins/AjaxError.vue'
import AjaxSpinner from '../mixins/AjaxSpinner.vue'

export default {
  name: 'ListComponent',
  components: {
    AjaxError, AjaxSpinner
  },
  data() {
    return {
      restURL: window.globals.restURL,
      error: false,
      loading: false,
      sort: {
        column: 'id',
        order: true
      },
      searchColumns: ['id', 'name'],
      searchString: '',

      ajaxUsers: false,
      ajaxRequest: null

    };
  },
  props: {
    acl: Array,
    list: Array
  },
  computed: {
    sortList: function () {
      if (this.list) {
        let data = this.list;
        if (data) {



          // SUCHE
          if (this.searchString != '') {
            let split = this.searchString.toLowerCase().split(' ');
            var search_temp = [];
            var search_result = [];
            this.searchColumns.forEach(function (col) {
              search_temp = data.filter((item) => {
                if (item[col] && typeof item[col] === 'string') {
                  return split.every(v => item[col].toLowerCase().includes(v));
                }
              });
              if (search_temp.length > 0) {
                search_result = Object.assign(search_result, search_temp);
              }
            });
            data = search_result;
          }
          //console.log(data.length)

          // USER nachladen
          if (data.length == 0) {
            this.loadUsers(this.searchString);
          }


          // SORTIERUNG
          if (this.sort.column) {
            if (typeof this.sort.column === 'string') {
              if (this.sort.column == 'date') {
                if (this.sort.order) {
                  return data.sort((a, b) => {
                    let aa = a[this.sort.column].split(' ');
                    let bb = b[this.sort.column].split(' ');
                    let date1 = new Date(aa[0].split('.')[2], aa[0].split('.')[1] - 1, aa[0].split('.')[0], aa[1].split(':')[0], aa[1].split(':')[1])
                    let date2 = new Date(bb[0].split('.')[2], bb[0].split('.')[1] - 1, bb[0].split('.')[0], bb[1].split(':')[0], bb[1].split(':')[1])
                    return date1 - date2;
                  })
                } else {
                  return data.sort((a, b) => {
                    let aa = a[this.sort.column].split(' ');
                    let bb = b[this.sort.column].split(' ');
                    let date1 = new Date(aa[0].split('.')[2], aa[0].split('.')[1] - 1, aa[0].split('.')[0], aa[1].split(':')[0], aa[1].split(':')[1])
                    let date2 = new Date(bb[0].split('.')[2], bb[0].split('.')[1] - 1, bb[0].split('.')[0], bb[1].split(':')[0], bb[1].split(':')[1])
                    return date2 - date1;
                  })
                }
              } else {
                if (this.sort.order) {
                  return data.sort((a, b) => {
                    if ( !isNaN(a[this.sort.column]) ) {
                      return a[this.sort.column] - b[this.sort.column];
                    } else {
                      return a[this.sort.column].localeCompare(b[this.sort.column])
                    }
                  })
                } else {
                  return data.sort((a, b) => {
                    if (b[this.sort.column] && a[this.sort.column]) {
                      if ( !isNaN(a[this.sort.column]) ) {
                        return b[this.sort.column] - a[this.sort.column];
                      } else {
                        return b[this.sort.column].localeCompare(a[this.sort.column])
                      }
                    }
                  })
                }
              }
            } else if (typeof this.sort.column === 'object') {
              if (this.sort.order) {
                return data.sort((a, b) => a[this.sort.column[0]][this.sort.column[1]].localeCompare(b[this.sort.column[0]][this.sort.column[1]]))
              } else {
                return data.sort((a, b) => b[this.sort.column[0]][this.sort.column[1]].localeCompare(a[this.sort.column[0]][this.sort.column[0]]))
              }
            }
          }




          return data;
        }
      }
      return [];
    }

  },
  created: function () {


  },
  methods: {

    handlerKillSearch() {
      this.searchString = '';
      this.ajaxUsers = false;
    },
    handlerRemove(item) {

      if (item && item.id) {
        this.$bus.$emit('item--delete', {
          item: item
        });
      }

    },
    handlerAdd(item) {

      if (item && item.id) {
        this.$bus.$emit('item--submit', {
          item: item
        });
      }

    },
    loadUsers(str) {

      if (str == false) {
        str = '*';
      }

      if (this.ajaxRequest ) {
        this.ajaxRequest.cancel();
      }
      this.ajaxRequest = axios.CancelToken.source();

      this.loading = true;
      var that = this;
      axios.get(this.restURL + str, { cancelToken: this.ajaxRequest.token })
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.ajaxUsers = response.data;
                that.loading = false;
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function (err) {

            if (axios.isCancel(err)) {
              //console.log('Previous request canceled, new request is send', err.message);
            } else {
              // handle error
              that.error = 'Fehler beim Laden. 02';
            }

          })
          .finally(function () {
            // always executed

          });

    },

    handlerOpen(item) {

      this.$bus.$emit('page--open', {
        page: 'item',
        item: item
      });

    },
    handlerSort: function (column) {
      if (column) {
        this.sort.column = column;
        if (this.sort.order) {
          this.sort.order = false;
        } else {
          this.sort.order = true;
        }
      }
    },

  }


};
</script>

<style>

</style>