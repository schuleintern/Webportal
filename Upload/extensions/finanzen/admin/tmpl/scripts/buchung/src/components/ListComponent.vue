<template>
  <div class="">

    <div class="">
      <select class="si-input margin-r-m" v-on:change="handlerFilter($event, 'state')">
        <option>- Status -</option>
        <option value="1">Offen</option>
        <option value="2">Gebucht</option>
      </select>

      <button class="si-btn" v-on:click="handlerRechnung()"><i class="fa fa-save"></i> Jetzt Rechnungen
        generieren</button>
    </div>

    <table class="si-table" v-if="sortList && sortList.length >= 1">
      <thead>
        <tr>
          <th v-on:click="handlerSort('dueDate')" class="curser-sort">Freigegeben</th>
          <th v-on:click="handlerSort('title')" class="curser-sort">Antrag</th>
          <th v-on:click="handlerSort('user_id')" class="curser-sort">Benutzer*in</th>
          <th v-on:click="handlerSort('amount')" class="curser-sort">Betrag</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  sortList" class="">
          <td width="10%">{{ item.createdTime }} - {{ item.createdUserID }}</td>
          <td>{{ item.title }}</td>
          <td>{{ item.user.name }}</td>
          <td>{{ item.amount }}</td>
          <td>{{ item.state }}
            <button v-if="item.state == 1" class="si-btn si-btn-off"><i class="fa fa-check"></i> Offen</button>
            <button v-if="item.state == 2" class="si-btn si-btn-off"><i class="fa fa-check"></i> Gebucht</button>
            <button v-if="item.state == 3" class="si-btn si-btn-off text-green"><i class="fa fa-check"></i> Bezahlt</button>
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
        column: 'id',
        order: true
      },
      searchColumns: ['id', 'title'],
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
                return split.every(v => item[col].toLowerCase().includes(v));
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
                  return data.sort((a, b) => a[this.sort.column].localeCompare(b[this.sort.column]))
                } else {
                  return data.sort((a, b) => b[this.sort.column].localeCompare(a[this.sort.column]))
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
    handlerRechnung: function () {

      this.$bus.$emit('action-rechnung');

    },
    handlerFilter: function (e, colum) {

      this.filter.colum = colum;
      this.filter.value = e.target.value;

    }

  }


};
</script>

<style></style>