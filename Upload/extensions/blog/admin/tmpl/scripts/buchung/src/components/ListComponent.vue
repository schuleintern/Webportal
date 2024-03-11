<template>
  <div class="">

    <div class="">
      <input type="search" v-model="searchString" class="si-input" placeholder="Suche...">

      <select class="si-input margin-l-m" v-on:change="handlerFilter($event, 'state')">
        <option value="">- Status -</option>
        <option value="1">Offen</option>
        <option value="2">Gebucht</option>
      </select>
    </div>


    <table class="si-table si-table-style-allLeft si-table-style-firstFix" v-if="sortList && sortList.length >= 1">
      <thead>
        <tr>
          <th v-on:click="handlerSort('id')" class="curser-sort" :class="{'text-orange': sort.column == 'id'}">ID</th>
          <th v-on:click="handlerSort('createdTime')" class="curser-sort" :class="{'text-orange': sort.column == 'createdTime'}">Freigegeben</th>
          <th v-on:click="handlerSort('orderNr')" class="curser-sort" :class="{'text-orange': sort.column == 'orderNr'}">Nr.</th>
          <th v-on:click="handlerSort('title')" class="curser-sort" :class="{'text-orange': sort.column == 'title'}">Antrag</th>
          <th v-on:click="handlerSort('userName')" class="curser-sort" :class="{'text-orange': sort.column == 'userName'}">Benutzer*in</th>
          <th v-on:click="handlerSort('amount')" class="curser-sort" :class="{'text-orange': sort.column == 'amount'}">Betrag</th>
          <th v-on:click="handlerSort('state')" class="curser-sort" :class="{'text-orange': sort.column == 'state'}">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  sortList" class="">
          <td>{{item.id}}</td>
          <td width="10%">{{ item.createdTime }} <div v-if="item.createdUserName" class="text-small text-grey">{{ item.createdUserName }}</div></td>
          <td>{{item.orderNr}}</td>
          <td>{{ item.title }}</td>
          <td>{{ item.userName }}</td>
          <td>{{ item.amount }} €</td>
          <td>
            <button v-if="item.state == 1" class="si-btn si-btn-off"><i class="fa fa-check"></i> Offen</button>
            <button v-if="item.state == 2" class="si-btn si-btn-off text-green"><i class="fa fa-check"></i> Bezahlt</button>
          </td>
          <td>
            <button v-if="item.state == 1" @click="handlerOpen(item)" class="si-btn">Details</button>
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
        order: false
      },
      searchColumns: ['id', 'title', 'userName','createdTime','orderNr','amount'],
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

    handlerFilter: function (e, colum) {

      this.filter.colum = colum;
      this.filter.value = e.target.value;

    }

  }


};
</script>

<style></style>