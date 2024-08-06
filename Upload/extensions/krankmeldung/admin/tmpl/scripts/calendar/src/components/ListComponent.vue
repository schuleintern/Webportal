<template>
  <div class="">
    <input type="search" class="si-input margin-r-s " v-model="searchString" placeholder="Suche..."/>

    <select class="si-input margin-l-m" v-on:change="handlerFilter($event, 'state')">
      <option value="">- Status -</option>
      <option value="0">offen</option>
      <option value="1">genehmigt</option>
      <option value="2">abgelehnt</option>
    </select>

    <div class="si-calendar margin-t-m">
      <div class="si-calendar-header">
        <button class="si-btn" @click="handlerDayPrev()"><i class="fa fa-arrow-left"></i> Zurück</button>
        <button class="si-btn si-btn-light" @click="handlerDayToday()"><i class="fa fa-home"></i>Heute</button>
        <div class="title">{{dateNice}}</div>
        <button class="si-btn" @click="handlerDayNext()"><i class="fa fa-arrow-right"></i> Weiter</button>
      </div>
    </div>

    <div class="flex-row">
      <div class="flex-2">
        <table class="si-table margin-0" v-if="sortList && sortList.length >= 1">
          <thead>
          <tr>
            <th v-on:click="handlerSort('state')" class="curser-sort" :class="{'text-orange': sort.column == 'state'}">Status</th>
            <th v-on:click="handlerSort('user_id')" class="curser-sort" :class="{'text-orange': sort.column == 'user_id'}">Benutzer*in</th>
            <th v-on:click="handlerSort('dateStart')" class="curser-sort" :class="{'text-orange': sort.column == 'dateStart'}">Von</th>
            <th v-on:click="handlerSort('dateEnd')" class="curser-sort" :class="{'text-orange': sort.column == 'dateEnd'}">Bis</th>
            <th v-on:click="handlerSort('days')" class="curser-sort" :class="{'text-orange': sort.column == 'days'}">Tage</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          <tr v-bind:key="index" v-for="(item, index) in  sortList" class="">
            <td>
              <button v-if="item.state == 0" class="si-btn si-btn-icon si-btn-curser-off si-btn-active"><i class="fa fa-question"></i></button>
              <button v-if="item.state == 1" class="si-btn si-btn-icon si-btn-curser-off si-btn-green"><i class="fa fa-check"></i></button>
              <button v-if="item.state == 2" class="si-btn si-btn-icon si-btn-curser-off si-btn-red"><i class="fa fa-ban"></i></button>
            </td>
            <td><User v-if="item.user" :data="item.user"></User></td>
            <td>{{ item.dateStartDate }}</td>
            <td>{{ item.dateEndDate }}</td>
            <td>{{ item.days }}</td>
            <td  style="text-align: left">
              <button  class="si-btn" @click="handlerItem(item)" :class="{'si-btn-active': item.openDetails }">Details</button>
            </td>
          </tr>
          </tbody>
        </table>
        <div v-else>
          <div class="padding-m"><i>- Noch keine Inhalte vorhanden -</i></div>
        </div>
      </div>
      <div class="flex-1 si-details margin-l-l" v-if="showDetails">

        <ul>
          <li v-if="item.info">
            <label>Bemerkung</label>
            {{item.info}}
          </li>

          <li>
            <label>Erstellt</label>
            {{ item.createdTime }}<br>{{ item.createdUserUser.name }}
          </li>

          <li v-if="item.lnw">
            <label>Leistungsnachweise</label>
            <ul class="">
              <li v-bind:key="i" v-for="(s, i) in  item.lnw" class="flex-row padding-0" >
                <div class="text-bold margin-r-m">{{s.art}}:</div>
                {{s.stunde}}. Stunde: {{s.fach}} - {{s.user}}
              </li>
            </ul>
          </li>

          <li v-if="item.plan">
            <label>Stundenplan</label>
            <ul>
              <li v-bind:key="i" v-for="(s, i) in  item.plan" class="flex-row padding-0" >
                <span class="text-bold margin-r-m">{{i+1}}: </span>
                <span v-bind:key="j" v-for="(stunde, j) in s" class="flex-1" >
                    {{stunde.subject}} - {{stunde.teacher}} - {{stunde.room}}
                </span>
              </li>
            </ul>
          </li>

        </ul>
      </div>
    </div>


    <br>



  </div>
</template>

<script>

import User from './../mixins/User.vue'


export default {
  name: 'ListComponent',
  components: {
    User
  },
  data() {
    return {
      sort: {
        column: 'id',
        order: false
      },
      searchColumns: ['id', 'dateStartDate', 'dateEndDate', 'info', 'userName','createdTime'],
      searchString: '',
      filter: {
        colum: false,
        value: false
      },
      showDetails: false,
      item: false

    };
  },
  props: {
    acl: Array,
    list: Array,
    date: String,
    dateNice: String
  },
  computed: {
    sortList: function () {
      if (this.list) {
        let data = this.list;
        if (data.length > 0) {

          // FILTER
          if (this.filter.colum && this.filter.value && this.filter.value != '') {
            let temp = data.filter((item) => {
              if (item[this.filter.colum] == this.filter.value) {
                return true;
              }
              return false;
            });
            data = temp;
          }

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
                    if (a[this.sort.column] && b[this.sort.column]) {
                      if (!isNaN(a[this.sort.column]) && !isNaN(b[this.sort.column])) {
                        return a[this.sort.column] - b[this.sort.column];
                      } else {
                        return a[this.sort.column].localeCompare(b[this.sort.column])
                      }
                    }
                  })
                } else {
                  return data.sort((a, b) => {
                    if (b[this.sort.column] && a[this.sort.column]) {
                      if (!isNaN(a[this.sort.column])) {
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

    handlerItem: function (item) {
      if (this.item) {
        this.item.openDetails = false;
      }

      this.showDetails = true;
      this.item = item;
      this.item.openDetails = true;
    },
    handlerDayPrev: function () {
      this.showDetails = false;
      this.$bus.$emit('page--load', {
        date: this.date,
        move: 'prev'
      })
    },
    handlerDayNext: function () {
      this.showDetails = false;
      this.$bus.$emit('page--load', {
        date: this.date,
        move: 'next'
      })
    },
    handlerDayToday: function () {
      this.showDetails = false;
      this.$bus.$emit('page--load', {
        date: this.date,
        move: ''
      })
    },
    handlerFilter: function (e, colum) {

      this.filter.colum = colum;
      this.filter.value = e.target.value;

    },

    handlerMouseover: function (item) {
      item.infoShow = true;
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

<style scoped>


</style>