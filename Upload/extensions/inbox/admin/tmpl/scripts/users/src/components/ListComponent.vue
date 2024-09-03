<template>
  <div class="">

    <button class="si-btn margin-r-m" @click="handlerAdd()"><i class="fa fa-plus"></i> Fehlende Benutzer hinzufügen</button>

    <input type="search" class="si-input margin-r-m" v-model="searchString" placeholder="Suche..."/>


    <select class="si-input" v-on:change="handlerFilter($event, 'user.type')">
      <option>- Benutzertyp -</option>
      <option value="isPupil">Schüler*in</option>
      <option value="isTeacher">Lehrer*in</option>
      <option value="isEltern">Eltern</option>
      <option value="isNone">Sonstige</option>
    </select>


    <table class="si-table si-table-style-firstLeft" v-if="sortList && sortList.length >= 1">
      <thead>
        <tr>
          <th>ID</th>
          <th v-on:click="handlerSort('title')" :class="{'text-orange': sort.column == 'title'}">Titel</th>
          <th v-on:click="handlerSort('user_id')" :class="{'text-orange': sort.column == 'user_id'}" >User ID</th>
          <th>Typ</th>
  
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  sortList" class="">
          <td>{{ item.id }}</td>
          <td><a :href="'#item'+item.id" v-on:click="handlerOpen(item)">{{item.userName}}</a></td>
          <td>{{ item.user_id }}</td>
          <td>
            <button v-if="item.user.type == 'isPupil'" class="si-btn si-btn-off si-btn-small">Schüler*in</button>
            <button v-if="item.user.type == 'isTeacher'" class="si-btn si-btn-off si-btn-small">Lehrer*in</button>
            <button v-if="item.user.type == 'isEltern'" class="si-btn si-btn-off si-btn-small">Eltern</button>
            <button v-if="item.user.type == 'isNone'" class="si-btn si-btn-off si-btn-small">Sonstiges</button>
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

export default {
  name: 'ListComponent',
  data() {
    return {

      sort: {
        column: 'userName',
        order: true
      },
      searchColumns: ['id', 'userName', 'user_id'],
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
              if (this.filter.colum.indexOf('.') >= 0) {
                var deep = item;
                let arr = this.filter.colum.split('.');
                arr.forEach((o) => {
                  if (deep[o]) {
                    deep = deep[o];
                  }
                })
                if (deep && deep == this.filter.value) {
                  return true;
                }
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


    handlerSet(item) {


      this.$bus.$emit('item--setOpen', {
        item: item
      });

    },
    handlerAdd() {

      this.$bus.$emit('page--makeUser');

    },
    handlerOpen(item) {

      this.$bus.$emit('page--open', {
        page: 'form',
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
    handlerFilter: function (e, colum) {

      this.filter.colum = colum;
      this.filter.value = e.target.value;

    }

  }


};
</script>

<style></style>