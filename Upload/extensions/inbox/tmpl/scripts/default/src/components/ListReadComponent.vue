<template>
  <div class="inbox-list-read">

    <div class="flex-row padding-b-s">
      <input type="search" v-model="searchString" class="si-input" placeholder="Suche...">
    </div>

    <div :class="{'height_35 scrollable-y': item != false }">
      <table class="si-table si-table-style-allLeft" v-if="sortList && sortList.length >= 1">
        <thead>
        <tr>
          <th v-on:click="handlerSort('id')" class="curser-sort"
              :class="{'text-orange colum-sort': sort.column == 'id'}"></th>
          <th v-on:click="handlerSort('isRead')" class="curser-sort"
              :class="{'text-orange colum-sort': sort.column == 'isRead'}">
          </th>
          <th v-on:click="handlerSort(['from','title'])" class="curser-sort">Sender</th>
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

        <tr v-bind:key="index" v-for="(msg, index) in  sortList" class="curser si-handler-parent"
            v-on:click="handlerOpen(msg)" :class="{'text-orange': msg.id == item.id}"
        >
          <td><span class="si-handler" style="cursor: move" draggable="true" @dragstart="startDrag($event, msg)"><i
              class="fa fa-grip-vertical"></i></span></td>
          <td>
            <div v-if="msg.isRead == 0" class="fa fa-star margin-r-m"></div>
            <div v-if="msg.priority == 1" class="fa fa-arrow-down text-green"></div>
            <div v-else-if="msg.priority == 2" class="fa fa-arrow-up text-red"></div>
          </td>
          <td>
            <span v-if="msg.from.user">{{ msg.from.user.name }}</span>
            <span v-else>{{ msg.from.title }}</span>
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

//import draggable from 'vuedraggable'

export default {
  name: 'ListComponent',
  components: {},
  data() {
    return {
      apiURL: window.globals.apiURL,
      sort: {
        column: 'date',
        order: false
      },
      searchColumns: ['id', 'subject', 'date', ['from', 'title']],
      searchString: '',
      drag: false,

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
        if (data.length > 0) {

          // SUCHE
          if (this.searchString != '') {
            let split = this.searchString.toLowerCase().split(' ');
            var search_temp = [];
            var search_result = [];
            this.searchColumns.forEach(function (col) {
              search_temp = data.filter((item) => {

                if (typeof col === 'string') {
                  if (item[col]) {
                    return split.every(v => item[col].toLowerCase().includes(v));
                  }
                } else if (typeof col === 'object') {
                  let obj = item;
                  col.forEach(function (deep) {
                    obj = obj[deep];
                  });
                  return split.every(v => obj.toLowerCase().includes(v));
                }
              });
              if (search_temp.length > 0) {
                search_result = Object.assign(search_result, search_temp);
              }
            });
            data = search_result;
          }

          // SORTIERUNG
          if ( this.sort.column) {
            if (typeof this.sort.column === 'string') {
              if (this.sort.column == 'date') {
                if ( this.sort.order) {
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

    startDrag(evt, item) {
      evt.dataTransfer.dropEffect = 'move'
      evt.dataTransfer.effectAllowed = 'move'
      evt.dataTransfer.setData('itemID', item.id)
    },


    handlerOpen(item) {

      this.$bus.$emit('message--read', {
        message: item
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