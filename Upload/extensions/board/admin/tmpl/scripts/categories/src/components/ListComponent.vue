<template>
  <div class="">


    <input type="search" class="si-input " v-model="searchString" placeholder="Suche..."/>

    <select class="si-input margin-l-m" v-on:change="handlerFilter($event, 'state')">
      <option value="">- Status -</option>
      <option value="0">Aus</option>
      <option value="1">An</option>
    </select>

    <button class="si-btn margin-l-l" @click="handlerOpen()"><i class="fa fa-plus"></i> Kategorie hinzufügen</button>


    <table class="si-table si-table-style-allLeft" v-if="sortList && sortList.length >= 1">
      <thead>
      <tr>
        <th v-on:click="handlerSort('title')" class="curser-sort" :class="{'text-orange': sort.column == 'title'}">
          Titel
        </th>
        <th v-on:click="handlerSort('sort')" class="curser-sort" :class="{'text-orange': sort.column == 'sort'}">
          Sortierung
        </th>
        <th v-on:click="handlerSort('state')" class="curser-sort" :class="{'text-orange': sort.column == 'state'}">
          Status
        </th>
      </tr>
      </thead>

      <draggable v-model="sortList" tag="tbody" handle=".sortHandle" group="sort" @start="drag=true" @end="drag=false"  >
        <template #item="{element}">
          <tr>
            <td><a  class="padding-l-m" :href="'#item'+element.id" v-on:click="handlerOpen(element)">{{ element.title }}</a></td>
            <td>
              <button class="sortHandle si-btn si-btn-icon" :class="{'si-btn-border': sort.column != 'sort'}"><i class="fas fa-sort"></i></button>
            </td>
            <td>
              <FormToggle :disable="false" :input="element.state"></FormToggle>
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
import FormToggle from '../mixins/FormToggle.vue'

export default {
  name: 'ListComponent',
  components: {
    FormToggle, draggable
  },
  data() {
    return {

      sort: {
        column: 'sort',
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
    sortList: {
      get: function () {

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
      },
      set: function (newVal) {

        if (this.sort.column == 'sort') {
          var a = 1;
          var post = [];
          newVal.forEach(function (o,) {
            o.sort = a;
            a++;
            post.push({
              "id": o.id,
              "sort": o.sort
            });
          });

          this.$bus.$emit('item--sort', {
            items: post
          });
        }


      }
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