<template>

  <div class="">

    <div class="flex-row">
      <div class="flex-1 flex-row">
        <button class="si-btn margin-r-m" v-on:click="handlerOpenForm()"><i
            class="fa fa-plus"></i>
          Neu
        </button>
      </div>
      <div class="flex-1 flex-row flex-end">
        <input type="search" class="si-input" v-model="searchString" placeholder="Suche..."/>
      </div>
    </div>

    <table v-if="vlist.length >= 1" class="si-table si-table-style-allLeft">
      <thead>
      <tr>
        <th width="1.5rem"></th>
        <th width="20%" v-on:click="handlerSort('title')" class="curser-sort">Titel</th>
        <th width="30%" class="">Info</th>
        <th width="" class="">Benutzer*innen</th>
        <th width="20%" class="">Bis</th>
        <th width="10%" class=""></th>
      </tr>
      </thead>
      <tbody>
      <tr v-bind:key="index" v-for="(item, index) in  vlist">
        <td class="text-grey text-small" :title="'ID: ' + item.id ">{{ index + 1 }}</td>
        <td class="">
          <a :href="'#'+item.id" v-on:click="handlerOpenItem(item)">{{ item.title }}</a>
        </td>
        <td class="padding-l-m">
          <span class="text-small">{{ item.info }}</span>
        </td>
        <td class="">
          <button class="si-btn si-btn-off text-bold">{{ item.members.length }}</button>
        </td>
        <td class="">
          {{ item.endDate }}
        </td>
        <td>
          <a :href="'#form-'+item.id"  class="si-btn si-btn-border si-btn-icon" v-on:click="handlerOpenForm(item)"><i class="fa fa-pen"></i></a>
        </td>

      </tr>
      </tbody>
    </table>
    <div v-else>
      <div class="si-hinweis ">Bisher keine Sammlung.</div>
    </div>


  </div>

</template>


<script>

export default {
  components: {},
  name: 'List',
  props: {
    items: Array,
    acl: Object
  },
  data() {
    return {

      showDays: window.globals.showDays,
      sort: {
        column: 'vorname',
        order: true
      },
      searchColumns: ['title','endDate'],
      searchString: '',

    }
  },
  created: function () {
  },
  mounted() {
  },
  computed: {
    vlist: function () {
      if (this.items) {

        let data = this.items;
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
            if (this.sort.order) {
              return data.sort((a, b) => {
                if (a[this.sort.column] && b[this.sort.column]) {
                  return a[this.sort.column].localeCompare(b[this.sort.column])
                }
              })
            } else {
              return data.sort((a, b) => {
                if (a[this.sort.column] && b[this.sort.column]) {
                  return b[this.sort.column].localeCompare(a[this.sort.column])
                }
              })
            }
          }


          return data;

        }

      }
      return [];
    }


  },
  methods: {

    handlerSort: function (column) {
      if (column) {
        this.sort.column = column;
        if (this.sort.order == true) {
          this.sort.order = false;
        } else {
          this.sort.order = true;
        }
      }
    },

    handlerOpenForm: function (item) {
      this.$bus.$emit('page--open', {
        page: 'form',
        item: item
      });
    },

    handlerOpenItem: function (item) {
      if (item) {
        this.$bus.$emit('page--open', {
          page: 'item',
          item: item
        });
      }
    }

  }
}
</script>
