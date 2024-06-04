<template>
  <div class="">

    <button v-if="acl.write == 1" class="si-btn" @click="handlerOpen()"><i class="fa fa-plus"></i> Hinzufügen</button>
    <table class="si-table" v-if="sortList && sortList.length >= 1">
      <thead>
      <tr>
        <th v-on:click="handlerSort('title')" class="curser-sort" :class="{'text-orange': sort.column == 'title'}" width="30%">Titel</th>
        <th v-on:click="handlerSort('state')" class="curser-sort" :class="{'text-orange': sort.column == 'state'}" width="5%">Status</th>
        <th></th>
        <th>Benutzer*innen</th>
        <th>Fragen</th>
        <th v-on:click="handlerSort('createdTime')" class="curser-sort" :class="{'text-orange': sort.column == 'createdTime'}">Erstellt</th>
      </tr>
      </thead>
      <tbody>
      <tr v-bind:key="index" v-for="(item, index) in  sortList" class="">
        <td><a :href="'#item'+item.id" v-on:click="handlerOpen(item)">{{ item.title }}</a></td>
        <td>
          <button v-if="item.state == 1" class="si-btn si-btn-off text-green si-btn-icon" >
            <i class="fa fas fa-toggle-on"></i></button>
          <button v-else class="si-btn si-btn-off si-btn-icon" >
            <i class="fa fas fa-toggle-off"></i></button>
        </td>
        <td><a :href="'#answer'+item.id" v-on:click="handlerAnswer(item)" class="si-btn si-btn-border"><i class="fa fa-poll"></i> Antworten</a></td>
        <td><span v-if="item.userlist">{{ item.userlist.length }}</span></td>
        <td><span v-if="item.childs">{{ item.childs.length }}</span></td>
        <td><span v-if="item.createdTime" class="text-small">{{ item.createdTime }}</span></td>
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
        column: 'createdTime',
        order: true
      },
      searchColumns: ['id', 'title'],
      searchString: ''

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

    handlerOpen(item) {

      this.$bus.$emit('page--open', {
        page: 'item',
        item: item
      });

    },
    handlerAnswer(item) {

      this.$bus.$emit('page--open', {
        page: 'answer',
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