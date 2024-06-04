<template>
  <div class="">

    <button v-if="acl.write == 1" class="si-btn margin-r-m" @click="handlerOpen()"><i class="fa fa-plus"></i> Hinzufügen</button>
    <button v-if="acl.write == 1" class="si-btn si-btn-light" @click="handlerAddFerien"><i class="fa fa-plus"></i><i class="fa fa-spa"></i> Ferien Hinzufügen</button>

    <table class="si-table si-table-style-allLeft" v-if="sortList && sortList.length >= 1">
      <thead>
      <tr>
        <th v-on:click="handlerSort('id')" class="curser-sort width-4rem">ID</th>
        <th v-on:click="handlerSort('state')" class="curser-sort width-12rem">Veröffentlicht</th>
        <th v-on:click="handlerSort('sort')" class="curser-sort width-12rem">Sortierung</th>
        <th v-on:click="handlerSort('title')" class="curser-sort">Titel</th>
        <th></th>
        <th class="width-12rem">Farbe</th>
        <th class="width-30rem"></th>

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
            </td>
            <td><a :href="'#item'+element.id" v-on:click="handlerOpen(element)">{{ element.title }}</a></td>
            <td><a v-if="!element.aclID" :href="'#item'+element.id" v-on:click="handlerOpen(element)"  class="si-btn si-btn-red"><i class="fa fa-user-shield margin-r-m"></i> Benutzerrechte</a></td>
            <td>
              <button class="si-btn si-btn-off si-btn-icon" :style="'background-color: '+element.color"></button>
            </td>
            <td>
              <button v-if="element.preSelect" class="si-btn si-btn-off si-btn-small si-btn-icon margin-r-s"><i class="fa fa-check-square"></i> Vorausgewählt
              </button>
              <button v-if="element.ferien" class="si-btn si-btn-off si-btn-small si-btn-icon margin-r-s"><i class="fa fas fa-spa"></i> Ferien
              </button>
              <button v-if="element.public" class="si-btn si-btn-off si-btn-small si-btn-icon margin-r-s"><i class="fa fa-rss-square"></i> ICS Feed
              </button>
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
            if (this.sort.order) {
              return data.sort((a, b) => ('' + a[this.sort.column]).localeCompare(b[this.sort.column]))
            } else {
              return data.sort((a, b) => ('' + b[this.sort.column]).localeCompare(a[this.sort.column]))
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

    handlerToggleChange: function (event, item) {
      item.state = event.value;

      this.$bus.$emit('item--state', {
        item: item
      });
    },

    handlerAddFerien: function () {
      this.$bus.$emit('item--add-holiday' );
    }


  }


};
</script>

<style>

</style>