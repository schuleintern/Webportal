<template>
  <div class="">

    <input type="search" class="si-input" v-model="searchString" placeholder="Suche..."/>

    <table class="si-table si-table-style-allLeft" v-if="sortList && sortList.length >= 1">
      <thead>
        <tr>
          <th v-on:click="handlerSort('username')" class="curser-sort" :class="{'text-orange': sort.column == 'username'}">Benutzer*in</th>
          <th v-on:click="handlerSort('total')" class="curser-sort" :class="{'text-orange': sort.column == 'total'}">Fehltage</th>

          <th v-bind:key="si" v-for="(slot, si) in  slots">
            {{ slot.tage }} Tage
          </th>

        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  sortList" class="">
          <td >
            <User v-if="item.user" :data="item.user"></User>
            <span v-else>UserID: {{ item.userID }}</span>
          </td>
          <td width="20%">{{ item.total }}</td>

          <td v-bind:key="si" v-for="(slot, si) in  slots">
            <div v-if="parseInt(slot.tage) <= parseInt(item.total)" class="si-hinweis">
              {{ slot.info }}
            </div>
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
        column: 'total',
        order: false
      },
      searchColumns: ['id','username'],
      searchString: ''

    };
  },
  props: {
    acl: Array,
    list: Array,
    slots: Array
  },
  computed: {
    sortList: function () {

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
                      if ( isNaN(a[this.sort.column]) ) {
                          return a[this.sort.column].localeCompare(b[this.sort.column]);
                      } else {
                        return parseInt(a[this.sort.column]) - parseInt(b[this.sort.column]);
                      }
                    }
                  })
                } else {
                  return data.sort((a, b) => {
                    if (a[this.sort.column] && b[this.sort.column]) {
                      if ( isNaN(a[this.sort.column]) ) {
                        return b[this.sort.column].localeCompare(a[this.sort.column]);
                      } else {
                        return parseInt(b[this.sort.column]) - parseInt(a[this.sort.column]);
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

    handlerAdd() {

      this.$bus.$emit('page--open', {
        page: 'form'
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

<style></style>