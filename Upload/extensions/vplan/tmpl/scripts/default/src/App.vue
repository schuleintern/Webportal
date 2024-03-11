<template>
  <div>

    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <div class="flex-row head">
      <input type="search" v-model="searchString" placeholder="Suche..." class="si-input blockInline margin-r-m"/>
      <div class="si-btn-multiple flex-row-nowrap mobile-margin-t-m" v-if="list && list.length >= 1">
        <button v-bind:key="index" v-for="(item, index) in  list"
                class="si-btn margin-r-s" :class="{'si-btn-active' : index == indexList}"
                v-on:click="handlerDate(item, index)">
          {{item.date}}
        </button>
      </div>
    </div>


    <div  v-if="list && list[indexList].day.text" v-html="list[indexList].day.text" class="margin-t-m"></div>

    <table class="si-table" v-if="vlist && vlist.length >= 1">
      <thead>
      <tr>
        <th v-on:click="handlerSort('stunde')" class="curser-sort">Stunde</th>
        <th v-on:click="handlerSort('klasse')" class="curser-sort" v-if="showCol['klasse']">Klasse(n)</th>
        <th v-on:click="handlerSort('user_neu')" class="curser-sort" v-if="showCol['user_neu']">Vertreterin</th>
        <th v-on:click="handlerSort('fach_neu')" class="curser-sort" v-if="showCol['fach_neu']">Fach</th>
        <th v-on:click="handlerSort('raum_neu')" class="curser-sort" v-if="showCol['raum_neu']">Raum</th>
        <th v-on:click="handlerSort('user_alt')" class="curser-sort" v-if="showCol['user_alt']">(Lehrer)</th>
        <th v-on:click="handlerSort('fach_alt')" class="curser-sort" v-if="showCol['fach_alt']">(Fach)</th>
        <th v-on:click="handlerSort('raum_alt')" class="curser-sort" v-if="showCol['raum_alt']">(Raum)</th>
        <th v-on:click="handlerSort('info_1')" class="curser-sort" v-if="showCol['info_1']">Info</th>
        <th v-on:click="handlerSort('info_2')" class="curser-sort" v-if="showCol['info_2']">Info</th>
        <th v-on:click="handlerSort('info_3')" class="curser-sort" v-if="showCol['info_3']">Info</th>
      </tr>
      </thead>
      <tbody  >
      <tr v-bind:key="index" v-for="(item, index) in  vlist"
          class="">
        <td>{{item.stunde}}</td>
        <td v-if="showCol['klasse']">{{item.klasse}}</td>
        <td v-if="showCol['user_neu']">{{item.user_neu}}</td>
        <td v-if="showCol['fach_neu']">{{item.fach_neu}}</td>
        <td v-if="showCol['raum_neu']">{{item.raum_neu}}</td>
        <td v-if="showCol['user_alt']"><span class="text-grey text-line">{{item.user_alt}}</span></td>
        <td v-if="showCol['fach_alt']"><span class="text-grey text-line">{{item.fach_alt}}</span></td>
        <td v-if="showCol['raum_alt']"><span class="text-grey text-line">{{item.raum_alt}}</span></td>
        <td v-if="showCol['info_1']">{{item.info_1}}</td>
        <td v-if="showCol['info_2']">{{item.info_2}}</td>
        <td v-if="showCol['info_3']">{{item.info_3}}</td>
      </tr>
      <tr v-if="vlist.length == 0">
        <td colspan="8"> - keine Inhalte vorhanden -</td>
      </tr>

      </tbody>
    </table>
    <div v-else class="padding-t-l">
      <i>- keine Inhalte -</i>
    </div>

  </div>

</template>

<script>

const axios = require('axios').default;

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'


export default {
  components: {
    AjaxError, AjaxSpinner
  },
  data() {
    return {
      apiURL: globals.apiURL,
      error: '',
      loading: false,

      list: false, // from AJAX

      indexList: false,

      sort: {
        column: 'stunde',
        order: true
      },
      searchColumns: ['klasse', 'stunde', 'user_neu', 'user_alt', 'fach_alt', 'fach_neu', 'raum_alt','raum_neu', 'info_1', 'info_2', 'info_3'],
      searchString: '',


      renderComponent: true,

      showCol: window.globals.showCol

    };
  },
  created: function () {

    this.loadList();

  },
  computed: {
    vlist: function () {
      if (this.indexList >= 0 && this.list[this.indexList] && this.list[this.indexList].data) {

        let data = this.list[this.indexList].data;
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
                    if ( !isNaN(a[this.sort.column]) ) {
                      return a[this.sort.column] - b[this.sort.column];
                    } else {
                      return a[this.sort.column].localeCompare(b[this.sort.column])
                    }
                  })
                } else {
                  return data.sort((a, b) => {
                    if (b[this.sort.column] && a[this.sort.column]) {
                      if ( !isNaN(a[this.sort.column]) ) {
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
  mounted() {

  },
  /*
  watch: {

    sort: {
      handler: function (val, oldVal) {

        console.log(val.order , oldVal.order);
        if (val.order != oldVal.order) {
          console.log('watch');
        }
      },
      deep: true
    }
  },
  */

  methods: {

    /*
    forceRerender() {
      // Removing my-component from the DOM
      this.renderComponent = false;

      this.$nextTick(() => {
        // Adding the component back in
        this.renderComponent = true;
      });
    },
    */

    handlerSort: function (column) {
      if (column) {
        this.sort.column = column;
        console.log('hand', this.sort.order);
        if (this.sort.order == true) {
          this.sort.order = false;
        } else {
          this.sort.order = true;
        }
        //this.forceRerender();
      }
    },

    handlerDate: function (item, index) {
      //console.log(index, item);
      this.indexList = index;

    },

    loadList: function () {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getList')
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.list = response.data;
                that.indexList = 0;
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Laden. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });

    }
  }

};
</script>

<style>


.isMobile .head {
  flex-direction: column !important;
}


.isMobile .head button span {
  display: none;
}

</style>