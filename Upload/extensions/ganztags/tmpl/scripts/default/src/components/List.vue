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
      <div class="flex-1"></div>
      <div class="flex-1 padding-l-l">
        <div>
          <label class="margin-r-s">Schülerinnen:</label>{{details.schueler_summe}}
        </div>
        <div>
          <label class="margin-r-s">Zählschülerinnen:</label>{{details.tage_zaehl}}
        </div>
      </div>
      <div class="flex-1 ">
        <div>
          <label class="margin-r-s">Tage:</label>{{details.tage_summe}}
        </div>
        <div>
          <label class="margin-r-s">Differenz:</label>{{details.tage_diff}}
        </div>
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
          {{ item.anz }} <span v-if="item.diff" class="text-small">({{ item.diff }})</span>
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
    groups: Array,
    details: Object
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
