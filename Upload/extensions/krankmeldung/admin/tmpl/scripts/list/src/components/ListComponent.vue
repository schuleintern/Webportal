<template>
  <div class="">
    <input type="search" class="si-input margin-r-s " v-model="searchString" placeholder="Suche..."/>

    <select class="si-input margin-l-m" v-on:change="handlerFilter($event, 'state')">
      <option value="">- Status -</option>
      <option value="0">offen</option>
      <option value="1">genehmigt</option>
      <option value="2">abgelehnt</option>
    </select>

    <table class="si-table" v-if="sortList && sortList.length >= 1">
      <thead>
      <tr>
        <th v-on:click="handlerSort('createdTime')" class="curser-sort" :class="{'text-orange': sort.column == 'createdTime'}">Erstellt</th>
        <th v-on:click="handlerSort('state')" class="curser-sort" :class="{'text-orange': sort.column == 'state'}">Status</th>
        <th v-on:click="handlerSort('user_id')" class="curser-sort" :class="{'text-orange': sort.column == 'user_id'}">Benutzer*in</th>
        <th v-on:click="handlerSort('dateStart')" class="curser-sort" :class="{'text-orange': sort.column == 'dateStart'}">Von</th>
        <th v-on:click="handlerSort('dateEnd')" class="curser-sort" :class="{'text-orange': sort.column == 'dateEnd'}">Bis</th>
        <th v-on:click="handlerSort('days')" class="curser-sort" :class="{'text-orange': sort.column == 'days'}">Tage</th>
        <th  style="text-align: left">Bemerkung</th>
      </tr>
      </thead>
      <tbody>
      <tr v-bind:key="index" v-for="(item, index) in  sortList" class="">
        <td class="text-small">{{ item.createdTime }}<br>{{ item.createdUserUser.name }}</td>
        <td>
          <button v-if="item.state == 0" class="si-btn si-btn-icon si-btn-curser-off si-btn-active"><i class="fa fa-question"></i></button>
          <button v-if="item.state == 1" class="si-btn si-btn-icon si-btn-curser-off si-btn-green"><i class="fa fa-check"></i></button>
          <button v-if="item.state == 2" class="si-btn si-btn-icon si-btn-curser-off si-btn-red"><i class="fa fa-ban"></i></button>
        </td>
        <td><User v-if="item.user" :data="item.user"></User></td>
        <td>{{ item.dateStartDate }}</td>
        <td>{{ item.dateEndDate }}</td>
        <td>{{ item.days }}</td>
        <td width="50%" style="text-align: left">
          <span v-if="item.info">
              <button v-if="!item.infoShow" class="si-btn si-btn-light"
                      @click="handlerMouseover(item)">Anzeigen</button>
              <div class="si-box" v-if="item.infoShow" v-html="item.info"></div>
            </span>
        </td>
      </tr>
      </tbody>
    </table>
    <div v-else>
      <div class="padding-m"><i>- Noch keine Inhalte vorhanden -</i></div>
    </div>

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
      }

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

<style>

</style>