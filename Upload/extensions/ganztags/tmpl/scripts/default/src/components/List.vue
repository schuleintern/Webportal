<template>

  <div class="">

    <div class="flex-row">
      <div class="flex-1 flex-row">
        <button v-if="unsigned.count > 0" class="si-btn margin-r-m" v-on:click="handlerOpenUnsigned()"><i
            class="fa fa-plus"></i>
          Fehlende Schüler hinzufügen
        </button>
        <input type="search" class="si-input" v-model="searchString" placeholder="Suche..."/>
      </div>
      <div class="flex-1 flex-row flex-end">
        <form action="index.php?page=ext_ganztags&view=default&task=printList" method="POST" target="_blank"
              class="">
          <input type="hidden" name="users" v-model="printList">
          <button class="si-btn si-btn-icon si-btn-border"><i class="fa fa-print"></i></button>
        </form>
      </div>
    </div>

    <table class="si-table si-table-style-allLeft">
      <thead>
      <tr>
        <th width="1rem"></th>
        <th width="20%" v-on:click="handlerSort('vorname')" class="curser-sort">Vorname</th>
        <th width="20%" v-on:click="handlerSort('nachname')" class="curser-sort">Nachname</th>
        <th width="5%" class="curser-sort"></th>
        <th width="10%" v-on:click="handlerSort('klasse')" class="curser-sort">Klasse</th>
        <th width="10%" v-on:click="handlerSort('anz')" class="curser-sort">Tage</th>
        <th v-bind:key="index" v-for="(item, index) in  showDays"><span
            v-if="item">{{ index.charAt(0).toUpperCase() + index.slice(1) }}</span></th>
        <th width="7%" v-on:click="handlerSort('info')" class="curser-sort">Info</th>
      </tr>
      </thead>
      <tbody>
      <tr v-bind:key="index" v-for="(item, index) in  vlist">
        <td class="text-grey text-small">{{ index + 1 }}</td>
        <td class="">
          <a :href="'#'+item.id" v-on:click="handlerOpenItem(item)">{{ item.vorname }}</a>
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
        <td class="">
          {{ item.anz }}
        </td>

        <td v-bind:key="i" v-for="(day, i) in  item.days">
          <div class="si-btn si-btn-off" v-if="day"
               :style="'color:'+getGroupColorByID(day.group)+'; border-color:'+getGroupColorByID(day.group)">
            {{ getGroupTitleByID(day.group) }}
          </div>
        </td>

        <td class="">
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
    acl: Object,
    groups: Array
  },
  data() {
    return {

      showDays: globals.showDays,
      sort: {
        column: 'vorname',
        order: true
      },
      searchColumns: ['vorname', 'nachname', 'klasse'],
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
    },
    printList: function () {
      var arr = [];
      this.vlist.forEach(function (o) {
        if (o.user_id) {
          arr.push(o.user_id);
        }
      });
      return arr;
    }


  },
  methods: {

    getGroupTitleByID: function (id) {
      var str = '';
      this.groups.forEach((o) => {
        if (o.id == id) {
          str = o.title;
        }
      });
      return str;
    },
    getGroupColorByID: function (id) {
      var str = '';
      this.groups.forEach((o) => {
        if (o.id == id) {
          str = o.color;
        }
      });
      return str;
    },
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
