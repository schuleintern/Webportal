<template>
  <div class="">

    <!--
    <button v-if="acl.write == 1" class="si-btn margin-r-m" @click="handlerOpen()"><i class="fa fa-plus"></i> Hinzufügen</button>
    -->
    <table class="si-table si-table-style-allLeft" v-if="sortList && sortList.length >= 1">
      <thead>
      <tr>
        <th v-on:click="handlerSort('id')" class="curser-sort width-4rem" :class="{'text-orange': sort.column == 'id'}">ID</th>
        <th v-on:click="handlerSort('state')" class="curser-sort width-12rem" :class="{'text-orange': sort.column == 'state'}">Veröffentlicht</th>
        <th v-on:click="handlerSort('sort')" class="curser-sort width-12rem" :class="{'text-orange': sort.column == 'sort'}">Sortierung</th>
        <th v-on:click="handlerSort('title')" class="curser-sort" :class="{'text-orange': sort.column == 'title'}">Titel</th>
        <th></th>
        <th class="width-12rem">Farbe</th>

      </tr>
      </thead>

      <draggable
          v-model="myList"
          tag="tbody"
          handle=".sort-handle"
          item-key="id">
        <template #item="{element}">
          <tr>
            <td class="text-small">{{ element.id }}</td>
            <td>
              <FormToggle  :input="element.state"
                           @change="handlerToggleChange($event, element)"></FormToggle>
            </td>
            <td>
              <button v-if="sort.column == 'sort'" class="sort-handle si-btn si-btn-off si-btn-icon"><i class="fa fa-sort"></i></button>
              <span v-else class="si-btn si-btn-off">{{element.sort}}</span>
            </td>
            <td><a :href="'#item'+element.id" v-on:click="handlerOpen(element)">{{ element.title }}</a></td>
            <td><a v-if="!element.aclID" :href="'#item'+element.id" v-on:click="handlerOpen(element)"  class="si-btn si-btn-red"><i class="fa fa-user-shield margin-r-m"></i> Benutzerrechte</a></td>
            <td>
              <button class="si-btn si-btn-off si-btn-icon" :style="'background-color: '+element.color"></button>
            </td>

          </tr>
        </template>
      </draggable>

    </table>
    <div v-else>
      <div class="padding-m"><i>- Noch keine Inhalte vorhanden -</i></div>
    </div>


  </div>
</template>

<script>
import draggable from 'vuedraggable'
import FormToggle from './../mixins/FormToggle.vue'

export default {
  name: 'ListComponent',
  components: {
    draggable,
    FormToggle
  },
  data() {
    return {
      sort: {
        column: 'sort',
        order: true
      },
      searchColumns: ['id', 'title'],
      searchString: '',
    };
  },
  props: {
    acl: Array,
    list: Array
  },
  computed: {
    myList: {
      get() {
        return this.sortList
      },
      set(value) {

        this.$bus.$emit('item--sort', {
          items: value
        });

      }
    },
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

    handlerOpen(item) {

      this.$bus.$emit('page--open', {
        page: 'item',
        item: item
      });

    },
    handlerSort: function (column) {
      //console.log(column, this.sort.order)
      if (column) {
        this.sort = {
          order: !this.sort.order,
          column: column
        }
      }
    },

    handlerToggleChange: function (event, item) {
      item.state = event.value;

      this.$bus.$emit('item--state', {
        item: item
      });
    },




  }


};
</script>

<style>

</style>