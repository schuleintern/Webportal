<template>
  <div class="">


    <input type="search" class="si-input " v-model="searchString" placeholder="Suche..."/>

    <select class="si-input margin-l-m" v-on:change="handlerFilter($event, 'type')">
      <option>- Type -</option>
      <option value="isPupil">Schüler*in</option>
      <option value="isEltern">Eltern</option>
      <option value="isTeacher">Lehrer*in</option>
      <option value="isNone">Sonstige</option>
    </select>

    <button v-if="acl.write == 1" class="si-btn margin-l-m" @click="handlerOpen()"><i class="fa fa-plus"></i> Hinzufügen</button>


    <table class="si-table si-table-style-allLeft" v-if="sortList && sortList.length >= 1">
      <thead>
      <tr>
        <th v-on:click="handlerSort('vorname')" class="curser-sort" :class="{'text-orange': sort.column == 'vorname'}">Vorname</th>
        <th v-on:click="handlerSort('nachname')" class="curser-sort" :class="{'text-orange': sort.column == 'nachname'}">Nachname</th>
        <th v-on:click="handlerSort('username')" class="curser-sort" :class="{'text-orange': sort.column == 'username'}">Benutzername</th>
        <th v-on:click="handlerSort('type')" class="curser-sort" :class="{'text-orange': sort.column == 'type'}">Type</th>
        <th v-on:click="handlerSort('klasse')" class="curser-sort" :class="{'text-orange': sort.column == 'klasse'}">Klasse</th>
        <th v-on:click="handlerSort('klasse')" class="curser-sort" :class="{'text-orange': sort.column == 'klasse'}">Klasse</th>
        <th v-on:click="handlerSort('id')" class="curser-sort" :class="{'text-orange': sort.column == 'id'}">ID</th>
      </tr>
      </thead>
      <tbody>
      <tr v-bind:key="index" v-for="(item, index) in  sortList" class="">
        <td>{{ item.vorname }}</td>
        <td><a :href="'#item'+item.id" v-on:click="handlerOpen(item)">{{ item.nachname }}</a></td>
        <td><a :href="'#item'+item.id" v-on:click="handlerOpen(item)">{{ item.username }}</a></td>
        <td>
          <button v-if="item.type=='isPupil'" class="si-btn si-btn-border si-btn-small">Schüler*in</button>
          <button v-if="item.type=='isEltern'" class="si-btn si-btn-border si-btn-small">Eltern</button>
          <button v-if="item.type=='isTeacher'" class="si-btn si-btn-border si-btn-small">Lehrer*in</button>
          <button v-if="item.type=='isNone'" class="si-btn si-btn-border si-btn-small">Sonstige</button>
        </td>
        <td>{{ item.klasse }}</td>
        <td>{{ item.email }}</td>
        <td>{{ item.id }}</td>
      </tr>
      </tbody>
    </table>
    <div v-else>
      <div class="padding-m"><i>- Noch keine Inhalte vorhanden -</i></div>
    </div>

  </div>
</template>

<script>

export default {
  name: 'ListComponent',
  data() {
    return {

      sort: {
        column: 'nachname',
        order: true
      },
      searchColumns: ['id', 'nachname','vorname','type','username','klasse'],
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
                    if (!isNaN(a[this.sort.column])) {
                      return a[this.sort.column] - b[this.sort.column];
                    } else {
                      return a[this.sort.column].localeCompare(b[this.sort.column])
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