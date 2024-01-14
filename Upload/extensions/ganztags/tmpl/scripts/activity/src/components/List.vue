<template>

  <div class="">


    <button v-if="unsigned.count > 0" class="si-btn" v-on:click="handlerOpenUnsigned()"><i class="fa fa-plus"></i>
      Fehlende Schüler hinzufügen
    </button>

    <input type="search" class="si-input"  v-model="searchString" placeholder="Suche..."  />

    <table class="si-table si-table-style-allLeft">
      <thead>
      <tr>
        <th width="20%" v-on:click="handlerSort('vorname')" class="curser-sort">Vorname</th>
        <th width="20%"v-on:click="handlerSort('nachname')" class="curser-sort">Nachname</th>
        <th width="5%" v-on:click="handlerSort('gender')" class="curser-sort"></th>
        <th width="10%" v-on:click="handlerSort('klasse')" class="curser-sort">Klasse</th>
        <th>Days</th>
        <th>Groups</th>
        <th>Info</th>
      </tr>
      </thead>
      <tbody>
      <tr v-bind:key="index" v-for="(item, index) in  vlist">
        <td class="">
          {{ item.vorname }}
        </td>
        <td class="padding-l-m">
          <a :href="'#'+item.id" v-on:click="handlerOpenItem(item)">{{ item.nachname }}</a>
        </td>
        <td class="">
          {{ item.gender }}
        </td>
        <td class="">
          {{ item.klasse }}
        </td>
        <td>
          {{ item.days }}
        </td>
        <td>
          {{ item.groups }}
        </td>
        <td>
          {{ item.info }}
        </td>
      </tr>
      </tbody>
    </table>


  </div>

</template>


<script>

export default {
  components: {},
  name: 'List',
  props: {
    items: Array,
    unsigned: Object,
    acl: Object
  },
  data() {
    return {

      sort: {
        column: 'vorname',
        order: true
      },
      searchColumns: ['vorname','nachname','klasse'],
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
                return split.every(v => item[col].toLowerCase().includes(v));
              });
              if (search_temp.length > 0) {
                search_result = Object.assign(search_result, search_temp);
              }
            });
            data = search_result;
          }



          // SORTIERUNG
          if (this.sort.column ) {
            if (this.sort.order) {
              return data.sort((a, b) => a[this.sort.column].localeCompare(b[this.sort.column]))
            } else {
              return data.sort((a, b) => b[this.sort.column].localeCompare(a[this.sort.column]))
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
        //console.log('hand', this.sort.order);
        if (this.sort.order == true) {
          this.sort.order = false;
        } else {
          this.sort.order = true;
        }
        //this.forceRerender();
      }
    },
    handlerSetFavorite: function (item) {
      if (item) {
        EventBus.$emit('item--favorite', {
          item: item
        });
      }
    },
    handlerOpenUnsigned: function () {
      EventBus.$emit('modal-unsigned--open');
    },
    handlerOpenItem: function (item) {
      if (item) {
        EventBus.$emit('item--open', {
          item: item
        });
      }
    }

  }
}
</script>
