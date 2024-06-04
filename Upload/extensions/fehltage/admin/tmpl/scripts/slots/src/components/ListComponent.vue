<template>
  <div class="">

    <table class="si-table" v-if="sortList && sortList.length >= 1">
      <thead>
      <tr>
        <th v-on:click="handlerSort('name')" class="curser-sort">Name</th>
        <th v-on:click="handlerSort('count')" class="curser-sort">Anzahl Schüler*innen</th>
      </tr>
      </thead>
      <tbody>
      <tr v-bind:key="index" v-for="(item, index) in  sortList" class="">
        <td><a :href="'#item'+item.id" v-on:click="handlerOpen(item)">{{ item.name }}</a></td>
        <td>{{ item.count }}</td>
        <td>
          <div v-bind:key="i" v-for="(leader, i) in  item.leader" class="">
            {{ leader.name }}
          </div>
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
        column: 'name',
        order: true
      },
      searchColumns: ['name'],
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
              if (this.sort.order) {
                return data.sort((a, b) => a[this.sort.column] - b[this.sort.column])
              } else {
                return data.sort((a, b) => b[this.sort.column] - a[this.sort.column])
              }
              /*
              if (this.sort.order) {
                return data.sort((a, b) => a[this.sort.column].localeCompare(b[this.sort.column]))
              } else {
                return data.sort((a, b) => b[this.sort.column].localeCompare(a[this.sort.column]))
              }
              */

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