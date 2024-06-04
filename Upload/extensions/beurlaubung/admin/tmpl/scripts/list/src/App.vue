<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <div v-if="page == 'list'">
      <input type="search" class="si-input" v-model="searchString" placeholder="Suche..."/>
      <table class="si-table" v-if="sortList && sortList.length >= 1">
        <thead>
        <tr>
          <th v-on:click="handlerSort('createdTime')" class="curser-sort" :class="{'text-orange': sort.column == 'createdTime'}">Erstellt</th>
          <th v-on:click="handlerSort('datumStart')" class="curser-sort" :class="{'text-orange': sort.column == 'datumStart'}">Datum</th>
          <th v-on:click="handlerSort('userID')" class="curser-sort" :class="{'text-orange': sort.column == 'userID'}">Schüler*in</th>
          <th v-on:click="handlerSort('stunden')" class="curser-sort" :class="{'text-orange': sort.column == 'stunden'}">Stunden</th>
          <th width="30%" >Begründung</th>
          <th v-on:click="handlerSort('status')" class="curser-sort" :class="{'text-orange': sort.column == 'status'}">Genehmigung</th>
          <th></th>
        </tr>
        </thead>
        <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  sortList"
            class="">
          <td>{{ item.createdTime }}</td>
          <td>{{ item.datumStart }}<span v-if="item.datumEnde && item.datumEnde != item.datumStart"> - {{ item.datumEnde }}</span></td>
          <td><span v-if="item.user">{{item.user.name}} <span class="text-small" v-if="item.user.klasse">( {{item.user.klasse}} )</span></span></td>
          <td>{{ item.stunden }}</td>
          <td>{{ item.info }}</td>
          <td v-if="item.status == 1">
            <button class="si-btn si-btn-off text-orange"><i class="fa fa-question"></i> Offen</button>
          </td>
          <td v-if="item.status == 2 || item.status == 21">
            <button class="si-btn si-btn-off text-green"><i class="fa fa-check"></i> Genehmigt</button>
          </td>
          <td v-if="item.status == 3">
            <button class="si-btn si-btn-off text-red"><i class="fa fa-ban"></i> Nicht Genehmigt</button>
          </td>
          <td v-if="item.doneDate">
            <div class="text-small text-bold" v-if="item.doneInfo">{{item.doneInfo}}</div>
            <div class="text-small" >{{item.doneDate}} <span v-if="item.doneUserUser">- {{item.doneUserUser.name}}</span></div>

            <div class="text-small text-bold" v-if="item.doneKLInfo">{{item.doneKLInfo}}</div>
            <div class="text-small" v-if="item.doneKLInfo && item.doneKLDate">{{item.doneKLDate}} <span v-if="item.doneUserUser">- {{item.doneUserUser.name}}</span></div>

            <div class="text-small text-bold" v-if="item.doneSLInfo">{{item.doneSLInfo}}</div>
            <div class="text-small" v-if="item.doneSLInfo && item.doneSLDate">{{item.doneSLDate}} <span v-if="item.doneUserUser">- {{item.doneUserUser.name}}</span></div>
          </td>
        </tr>
        </tbody>
      </table>
      <div v-else> - keine Inhalte vorhanden -</div>
    </div>

  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

import '@vuepic/vue-datepicker/dist/main.css'

const axios = require('axios').default;



export default {
  setup() {

    return {

    }
  },
  name: 'App',
  components: {
    AjaxError, AjaxSpinner
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      settings: window.globals.settings,
      error: false,
      loading: false,
      page: 'list',
      sort: {
        column: 'datumStart',
        order: false
      },
      sortDates: ['datumStart','createdTime'],
      searchColumns: ['datumStart', 'datumEnde','stunden','info','doneInfo','username'],
      searchString: '',

      list: false

    };
  },
  computed: {
    sortList: function () {

      function getDates(a, b) {
        const dmyA = a[that.sort.column].split(".");
        const date1 = new Date(dmyA[2], dmyA[1] - 1, dmyA[0]);
        const dmyB = b[that.sort.column].split(".");
        const date2 = new Date(dmyB[2], dmyB[1] - 1, dmyB[0]);
        return [date1, date2];
      }
      if (this.list) {
        let data = this.list;
        if (data.length > 0) {

          // SUCHE
          if (this.searchString != '') {
            let split = this.searchString.toLowerCase().split(' ');
            var search_temp = [];
            var search_result = [];
            this.searchColumns.forEach(function (col) {
              search_temp = data.filter((item) => {
                if (item[col]) {
                  return split.every(v => item[col].toLowerCase().includes(v));
                }
              });
              if (search_temp.length > 0) {
                search_result = Object.assign(search_result, search_temp);
              }
            });
            data = search_result;
          }

          // SORTIERUNG
          var that = this;
          if (this.sort.column) {
            if (this.sort.order) {
              //return data.sort((a, b) => a[this.sort.column].localeCompare(b[this.sort.column]))
              return data.sort( function (a, b) {
                if ( that.sortDates.includes(that.sort.column) ) {
                  var dates = getDates(a,b);
                  return dates[0] - dates[1];
                } else {
                  return a[that.sort.column].localeCompare(b[that.sort.column]);
                }
              });
            } else {
              //return data.sort((a, b) => b[this.sort.column].localeCompare(a[this.sort.column]))
              return data.sort( function (a, b) {
                if ( that.sortDates.includes(that.sort.column) ) {
                  var dates = getDates(a,b);
                  return dates[1] - dates[0];
                } else {
                  return b[that.sort.column].localeCompare(a[that.sort.column]);
                }
              });
            }
          }

          return data;
        }
      }
      return [];
    }

  },
  created: function () {

    this.loadList();

  },
  methods: {

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
    loadList() {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getListAdmin/list')
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
    handlerSaveForm(e) {

      e.preventDefault();

      if (this.form.stunden.length < 1 && this.form.schueler && this.form.date) {
        return false;
      }

      const formData = new FormData();
      formData.append('stunden', this.form.stunden);
      formData.append('schueler', this.form.schueler);
      formData.append('date', this.form.date);
      formData.append('info', this.form.info);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setAntrag', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
          .then(function (response) {
            if (response.data) {
console.log(response.data);
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                this.loadList();
                this.handlerPage();
                //data.item.favorite = response.data.favorite;
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
    handlerSetStunden(e, arr) {
      e.preventDefault();

      arr.forEach((stunde) => {
        if (this.form.stunden.includes(stunde)) {
          this.form.stunden.splice(this.form.stunden.indexOf(stunde), 1);
        } else {
          this.form.stunden.push(stunde);
        }
      })
      this.form.stunden.sort(function (a, b) {
        return a - b
      });
    },
    inStunden(stunde) {
      if (this.form.stunden.includes(stunde)) {
        return true;
      }
      return false;
    },
    handleDate(newDate) {
      this.form.date = newDate;
    }
  }
}
</script>

<style>

.dp__theme_light {
  --dp-background-color: #ffffff;
  --dp-text-color: #212121;
  --dp-hover-color: #f3f3f3;
  --dp-hover-text-color: #212121;
  --dp-hover-icon-color: #959595;
  --dp-primary-color: #3c8dbc;
  --dp-primary-text-color: #f8f5f5;
  --dp-secondary-color: #c0c4cc;
  --dp-border-color: #ddd;
  --dp-menu-border-color: #ddd;
  --dp-border-color-hover: #aaaeb7;
  --dp-disabled-color: #f6f6f6;
  --dp-scroll-bar-background: #f3f3f3;
  --dp-scroll-bar-color: #959595;
  --dp-success-color: #018d4e;
  --dp-success-color-disabled: #a3d9b1;
  --dp-icon-color: #959595;
  --dp-danger-color: #dd4b39;
}

.dp__action.dp__cancel,
.dp__action.dp__select {
  box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 15px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px;
  border-radius: 3rem;

  display: inline-block;

  padding: 1rem 1.6rem;
  margin-bottom: 0.3rem;
  margin-top: 0.3rem;

  font-size: 11pt;
  font-weight: 300;
  line-height: 100%;
  letter-spacing: 0.75pt;
  text-align: center;


  vertical-align: middle;
  -ms-touch-action: manipulation;
  touch-action: manipulation;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  background-image: none;

  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  background-color: #3c8dbc;
  border: 1px solid #3c8dbc;
  color: #fff;
}

.dp__action.dp__cancel {
  background-color: #b7c7ce;
  border-color: #b7c7ce;
  color: #fff;
  margin-right: 1rem;
}

.dp__action_row {
  flex-direction: column;
}

.dp__selection_preview,
.dp__action_buttons {
  flex: 1;
  width: 100%;
}

.dp__menu {
  font-size: inherit;
}

.dp__selection_preview {
  font-size: inherit;
  display: flex;
  justify-content: space-around;
}

.dp__input_icons {
  width: 1.5rem;
  height: 1.5rem;
  margin-left: 1rem;
  margin-right: 0.3rem;
}

.dp__input {
  padding-left: 5rem !important;
}
</style>
