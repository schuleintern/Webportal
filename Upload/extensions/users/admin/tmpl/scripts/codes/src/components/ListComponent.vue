<template>
  <div class="">


    <input type="search" class="si-input " v-model="searchString" placeholder="Suche..."/>

    <button class="si-btn margin-l-l" @click="handlerOpen()"><i class="fa fa-plus"></i> Elterncode hinzufügen</button>

    <table class="si-table si-table-style-allLeft" v-if="sortList && sortList.length >= 1">
      <thead>
      <tr>
        <th v-on:click="handlerSort('codeID')" class="curser-sort" :class="{'text-orange': sort.column == 'codeID'}">
          ID
        </th>
        <th v-on:click="handlerSort('codeUserID')" class="curser-sort"
            :class="{'text-orange': sort.column == 'codeUserID'}">Benutzer
        </th>
        <th v-on:click="handlerSort('codeText')" class="curser-sort"
            :class="{'text-orange': sort.column == 'codeText'}">Code
        </th>
        <th v-on:click="handlerSort('codePrinted')" class="curser-sort"
            :class="{'text-orange': sort.column == 'codePrinted'}">Gedruckt
        </th>

        <th v-on:click="handlerSort('codeSchuelerAsvID')" class="curser-sort"
            :class="{'text-orange': sort.column == 'codeSchuelerAsvID'}">Schüler AsvID
        </th>
        <th>Schülerbenutzer</th>
        <th>Schüler UserID</th>
        <th></th>

      </tr>
      </thead>
      <tbody>
      <tr v-bind:key="index" v-for="(item, index) in  sortList" class="">
        <td>{{ item.codeID }}</td>
        <td>
          <div v-bind:key="i" v-for="(obj, i) in  item.user">
            <User v-if="obj.name" :data="obj"></User>
            <span v-else class="text-small">Gelöscht: User ID {{ item.codeUserID }}</span>
          </div>
        </td>
        <td>{{ item.codeText }}</td>
        <td>{{ item.codePrinted }}</td>
        <td>{{ item.codeSchuelerAsvID }}</td>

        <td>
          <User v-if="item.schuelerUser" :data="item.schuelerUser"></User>
        </td>
        <td><span v-if="item.schuelerUser">{{ item.schuelerUser.id }}</span></td>
        <td>
          <a class="si-btn" :href="'index.php?page=ext_users&view=codes&admin=true&task=print&a='+item.codeID" ><i class="fa fa-print"></i>Drucken</a>
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

import User from "@/mixins/User.vue";

export default {
  name: 'ListComponent',
  components: {
    User
  },
  data() {
    return {

      sort: {
        column: 'codePrinted',
        order: true
      },
      searchColumns: ['codeID', 'elternEMail', 'elternSchuelerAsvID', 'elternUserName', 'schuelerUserName'],
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