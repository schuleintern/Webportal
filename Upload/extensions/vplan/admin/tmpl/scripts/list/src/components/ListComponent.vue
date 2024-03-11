<template>
  <div class="">


    <table class="si-table si-table-style-firstLeft" v-if="sortList && sortList.length >= 1">
      <thead>
      <tr>
        <th v-on:click="handlerSort('date')" class="curser-sort" :class="{'text-orange': sort.column == 'date'}">Datum</th>
        <th v-on:click="handlerSort('stunde')" class="curser-sort" :class="{'text-orange': sort.column == 'stunde'}">Stunde</th>
        <th v-on:click="handlerSort('klasse')" class="curser-sort" v-if="showCol['klasse']" :class="{'text-orange': sort.column == 'klasse'}">Klasse(n)</th>
        <th v-on:click="handlerSort('user_neu')" class="curser-sort" v-if="showCol['user_neu']" :class="{'text-orange': sort.column == 'user_neu'}">Vertreterin</th>
        <th v-on:click="handlerSort('fach_neu')" class="curser-sort" v-if="showCol['fach_neu']" :class="{'text-orange': sort.column == 'fach_neu'}">Fach</th>
        <th v-on:click="handlerSort('raum_neu')" class="curser-sort" v-if="showCol['raum_neu']" :class="{'text-orange': sort.column == 'raum_neu'}">Raum</th>
        <th v-on:click="handlerSort('user_alt')" class="curser-sort" v-if="showCol['user_alt']" :class="{'text-orange': sort.column == 'user_alt'}">(Lehrer)</th>
        <th v-on:click="handlerSort('fach_alt')" class="curser-sort" v-if="showCol['fach_alt']" :class="{'text-orange': sort.column == 'fach_alt'}">(Fach)</th>
        <th v-on:click="handlerSort('raum_alt')" class="curser-sort" v-if="showCol['raum_alt']" :class="{'text-orange': sort.column == 'raum_alt'}">(Raum)</th>
        <th v-on:click="handlerSort('info_1')" class="curser-sort" v-if="showCol['info_1']" :class="{'text-orange': sort.column == 'info_1'}">Info</th>
        <th v-on:click="handlerSort('info_2')" class="curser-sort" v-if="showCol['info_2']" :class="{'text-orange': sort.column == 'info_2'}">Info</th>
        <th v-on:click="handlerSort('info_3')" class="curser-sort" v-if="showCol['info_3']" :class="{'text-orange': sort.column == 'info_3'}">Info</th>
      </tr>
      </thead>
      <tbody  >
      <tr v-bind:key="index" v-for="(item, index) in  sortList"
          class="">
        <td>{{item.date}}</td>
        <td>{{item.stunde}}</td>
        <td v-if="showCol['klasse']">{{item.klasse}}</td>
        <td v-if="showCol['user_neu']">{{item.user_neu}}</td>
        <td v-if="showCol['fach_neu']">{{item.fach_neu}}</td>
        <td v-if="showCol['raum_neu']">{{item.raum_neu}}</td>
        <td v-if="showCol['user_alt']"><span class="text-grey text-line">{{item.user_alt}}</span></td>
        <td v-if="showCol['fach_alt']"><span class="text-grey text-line">{{item.fach_alt}}</span></td>
        <td v-if="showCol['raum_alt']"><span class="text-grey text-line">{{item.raum_alt}}</span></td>
        <td v-if="showCol['info_1']">{{item.info_1}}</td>
        <td v-if="showCol['info_2']">{{item.info_2}}</td>
        <td v-if="showCol['info_3']">{{item.info_3}}</td>
      </tr>

      </tbody>
    </table>
    <div v-else class="padding-t-l">
      <i>- keine Inhalte -</i>
    </div>



  </div>
</template>

<script>

export default {
  name: 'ListComponent',
  data() {
    return {

      sort: {
        column: 'date',
        order: false
      },
      searchColumns: ['id', 'title'],
      searchString: ''

    };
  },
  props: {
    acl: Array,
    list: Array,
    showCol: Array
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
                    let date1 = new Date(aa[0].split('.')[2], aa[0].split('.')[1] - 1, aa[0].split('.')[0])
                    let date2 = new Date(bb[0].split('.')[2], bb[0].split('.')[1] - 1, bb[0].split('.')[0])
                    return date1 - date2;
                  })
                } else {
                  return data.sort((a, b) => {
                    let aa = a[this.sort.column].split(' ');
                    let bb = b[this.sort.column].split(' ');
                    let date1 = new Date(aa[0].split('.')[2], aa[0].split('.')[1] - 1, aa[0].split('.')[0])
                    let date2 = new Date(bb[0].split('.')[2], bb[0].split('.')[1] - 1, bb[0].split('.')[0])
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
                    if ( !isNaN(a[this.sort.column]) ) {
                      return b[this.sort.column] - a[this.sort.column];
                    } else {
                      return b[this.sort.column].localeCompare(a[this.sort.column])
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

    handlerDelete(item) {
      if (item.id) {
        this.$bus.$emit('item--setDelete', {
          item: item
        });
      }
    },
    handlerSetStatus(item) {
      if (item.id) {
        this.$bus.$emit('item--setStatus', {
          item: item
        });
      }
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