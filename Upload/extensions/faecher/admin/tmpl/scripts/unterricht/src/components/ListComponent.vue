<template>
  <div class="">

    <input type="search" class="si-input " v-model="searchString" placeholder="Suche..."/>

    <select class="si-input margin-l-m" v-on:change="handlerFilter($event, 'klassen')">
      <option>- Klassen -</option>
      <option v-bind:key="index" v-for="(item, index) in  klassenList" class=""  :value="item">{{item}}</option>
    </select>

    <select class="si-input margin-l-m" v-on:change="handlerFilter($event, 'art')">
      <option>- Art -</option>
      <option value="Qualifikationsphase">Qualifikationsphase</option>
      <option value="Wahlunterricht">Wahlunterricht</option>
      <option value="Pflichtunterricht">Pflichtunterricht</option>
      <option value="Förderunterricht">Förderunterricht</option>
    </select>

    <table class="si-table si-table-style-allLeft" v-if="sortList && sortList.length >= 1">
      <thead>
      <tr>
        <th v-on:click="handlerSort('desc')" class="curser-sort" :class="{'text-orange': sort.column == 'desc'}">Titel
        </th>
        <th v-on:click="handlerSort('klassen')" class="curser-sort" :class="{'text-orange': sort.column == 'klassen'}">
          Klasse
        </th>
        <th v-on:click="handlerSort('teacherID')" class="curser-sort"
            :class="{'text-orange': sort.column == 'teacherID'}">Lehrer*inn
        </th>
        <th v-on:click="handlerSort('fachID')" class="curser-sort" :class="{'text-orange': sort.column == 'fachID'}">
          Fach
        </th>
        <th v-on:click="handlerSort('art')" class="curser-sort" :class="{'text-orange': sort.column == 'art'}">Art</th>
        <th v-on:click="handlerSort('stunden')" class="curser-sort" :class="{'text-orange': sort.column == 'stunden'}">
          Stunden
        </th>
      </tr>
      </thead>
      <tbody>
      <tr v-bind:key="index" v-for="(item, index) in  sortList" class="">
        <!--
                <td><a :href="'#item'+item.id" v-on:click="handlerOpen(item)">{{ item.long }}</a></td>
                -->
        <td><span class="padding-l-m">{{ item.desc }}</span></td>
        <td>{{ item.klassen }}</td>
        <td>{{ item.teacherID }}</td>
        <td>{{ item.fachID }}</td>
        <td>{{ item.art }}</td>
        <td>{{ item.stunden }}</td>
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
  components: {},
  data() {
    return {

      sort: {
        column: 'desc',
        order: true
      },
      searchColumns: ['id', 'asvID', 'klassen', 'art'],
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
    klassenList: function () {
      if (this.list) {
        let data = this.list;
        if (data.length > 0) {
          let arr = [];
          data.forEach((o) => {
            if ( o['klassen'] && !arr.includes( o['klassen'] )) {
              arr.push(o['klassen']);
            }
          })
          return arr.sort( function (w1, w2) {
            return parseInt(w1) - parseInt(w2);
          });
        }
      }
      return [];
    },
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