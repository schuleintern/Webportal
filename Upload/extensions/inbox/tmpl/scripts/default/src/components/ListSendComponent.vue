<template>
  <div class="inbox-list-send">

    <div :class="{'height_35 scrollable-y': item != false }">
      <table class="si-table si-table-style-allLeft" v-if="sortList && sortList.length >= 1">
        <thead>
        <tr>
          <th v-on:click="handlerSort('id')" class="curser-sort"
              :class="{'text-orange colum-sort': sort.column == 'id'}"></th>
          <th v-on:click="handlerSort('isRead')" class="curser-sort"
              :class="{'text-orange colum-sort': sort.column == 'isRead'}">Status
          </th>
          <th >
            Empfänger
          </th>
          <th v-on:click="handlerSort('subject')" class="curser-sort"
              :class="{'text-orange colum-sort': sort.column == 'subject'}">Betreff
          </th>
          <th v-on:click="handlerSort('files')" class="curser-sort"
              :class="{'text-orange colum-sort': sort.column == 'files'}">Anhang
          </th>
          <th v-on:click="handlerSort('date')" class="curser-sort"
              :class="{'text-orange colum-sort': sort.column == 'date'}">Datum
          </th>
        </tr>
        </thead>
        <tbody>
        <tr v-bind:key="index" v-for="(msg, index) in  sortList" class="curser"
            v-on:click="handlerOpen(msg)" :class="{'text-orange': msg.id == item.id}">
          <td></td>
          <td>
            <div v-if="msg.isRead == 0" class="fa fa-star margin-r-m"></div>
            <div v-if="msg.priority == 1" class="fa fa-arrow-down text-green"></div>
            <div v-else-if="item.priority == 2" class="fa fa-arrow-up text-red"></div>
          </td>
          <td>
            <span v-bind:key="i" v-for="(inbox, i) in  msg.to">
              {{ inbox.title }} ({{ inbox.count }})
            </span>
            <!--
            <div v-if="item.to && item.to.length <= 3">
              <div v-bind:key="i" v-for="(inbox, i) in  item.to">
                <span v-if="inbox.user">{{ inbox.user.name }}</span>
                <span v-else>{{ inbox.title }}</span>
                <span v-if="i+1 < item.to.length">, </span>
              </div>
            </div>
            <div v-else>
              Mehrere Empfänger ({{item.to.length}})
            </div>
            -->

            <div v-if="msg.toCC" class="text-grey">
              <label>CC: </label>
              <span v-bind:key="i" v-for="(inbox, i) in  msg.toCC">
                <span v-if="inbox.user">{{ inbox.user.name }}</span>
                <span v-else>{{ inbox.title }}</span>
                <span v-if="i+1 < msg.toCC.length">, </span>
              </span>
            </div>

          </td>
          <td :class="{'text-bold': msg.isRead == 0}">{{ msg.subject }}</td>
          <td><i v-if="msg.files" class="fa fa-paperclip"></i></td>
          <td>{{ msg.date }}</td>

        </tr>
        </tbody>
      </table>
      <div v-else>
        <div class="padding-m"><i>- Noch keine Inhalte vorhanden -</i></div>
      </div>
    </div>

  </div>
</template>

<script>

export default {
  name: 'ListComponent',
  data() {
    return {
      apiURL: window.globals.apiURL,
      sort: {
        column: 'date',
        order: false
      },
      searchColumns: ['id', 'subject', 'date'],
      searchString: '',

    };
  },
  props: {
    acl: Array,
    list: Array,
    item: Array
  },
  computed: {
    sortList: function () {
      if (this.list) {
        let data = this.list;
        if (data.length > 0 && data.sort) {

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
                    let date1 = new Date(aa[0].split('.')[2], aa[0].split('.')[1]-1, aa[0].split('.')[0], aa[1].split(':')[0], aa[1].split(':')[1])
                    let date2 = new Date(bb[0].split('.')[2], bb[0].split('.')[1]-1, bb[0].split('.')[0], bb[1].split(':')[0], bb[1].split(':')[1])
                    return date1 - date2;
                  })
                } else {
                  return data.sort((a, b) => {
                    let aa = a[this.sort.column].split(' ');
                    let bb = b[this.sort.column].split(' ');
                    let date1 = new Date(aa[0].split('.')[2], aa[0].split('.')[1]-1, aa[0].split('.')[0], aa[1].split(':')[0], aa[1].split(':')[1])
                    let date2 = new Date(bb[0].split('.')[2], bb[0].split('.')[1]-1, bb[0].split('.')[0], bb[1].split(':')[0], bb[1].split(':')[1])
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



    handlerOpen(item) {

      this.$bus.$emit('message--read', {
        message: item
      });

    },
    handlerSort: function (column) {
      if (column) {
        this.sort.column = column;
        this.sort.order = !this.sort.order;
      }
    },

  }


};
</script>

<style>

</style>