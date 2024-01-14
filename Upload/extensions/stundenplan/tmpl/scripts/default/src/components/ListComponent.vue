<template>
  <div class="">

    <div class="flex-row head">

      <div class="flex-row filter" v-if="defaults.showFilter === true">
        <input type="search" v-model="searchString" @keyup="handlerSearch" placeholder="Suche..." class="si-input margin-r-l width-12rem"/>
        <div class="toolbar si-btn-multiple margin-r-m ">
          <button class="si-btn si-btn-icon si-btn-light margin-r-xs" :class="{'si-btn-active': 'teacher' == openTab }" @click="handlerOpenSelect('teacher')"><i class="fa fa-user"></i></button>
          <button class="si-btn si-btn-icon si-btn-light margin-r-xs" :class="{'si-btn-active': 'class' == openTab }" @click="handlerOpenSelect('class')"><i class="fa fa-users"></i></button>
          <button class="si-btn si-btn-icon si-btn-light margin-r-xs" :class="{'si-btn-active': 'fach' == openTab }" @click="handlerOpenSelect('fach')"><i class="fa fa-flask"></i></button>
          <button class="si-btn si-btn-icon si-btn-light margin-r-xs" :class="{'si-btn-active': 'room' == openTab }" @click="handlerOpenSelect('room')"><i class="fa fa-door-open"></i></button>
        </div>
      </div>

      <div class="si-btn-multiple margin-r-m mobile-margin-t-s" v-if="myKlassen.length > 1">
        <button class="si-btn margin-r-xs" :class="{'si-btn-active': item == list.active }"
          v-bind:key="index" v-for="(item, index) in  myKlassen" 
          @click="handlerChangeClass(item)" >{{ item }}</button>
      </div>

      <div class="mobile-margin-t-s">
        <button v-if="toggleVplan == 1" class="si-btn si-btn-icon si-btn-toggle-on" v-on:click="handlerToggleVplan">
          <i class="fa fas fa-toggle-on"></i> <i class="fa fa-retweet"></i></button>
        <button v-else class="si-btn si-btn-icon si-btn-toggle-off" v-on:click="handlerToggleVplan">
          <i class="fa fas fa-toggle-off"></i> <i class="fa fa-retweet"></i></button>
      </div>

      <div class="title flex-1 flex-row flex-end padding-r-l">
        <h3>{{ list.title }}</h3>
      </div>
      
    </div>

    <div v-if="searchResult">

      <div class="columns-6 mobile-colums-3 padding-t-l">

          <button v-bind:key="index" v-for="(item, index) in  searchResult" class="si-btn si-btn-border width-100p" @click="handlerChangePlan(item)">
            <i v-if="item[1] == 'subject'" class="fa fa-flask"></i>
            <i v-if="item[1] == 'room'" class="fa fa-door-open"></i>
            <i v-if="item[1] == 'grade'" class="fa fa-users"></i>
            <i v-if="item[1] == 'teacher'" class="fa fa-user"></i>
   
            <span v-if="isMobile && item[2]">{{ item[2] }}</span>
            <span v-else>{{ item[0] }}</span>
          </button>
  
      </div>

    </div>

    <div v-else>

      <table class="si-table si-table-style-firstLeft" v-if="plan && plan.length >= 1">

        <thead>
          <tr>
            <td></td>
            <td :class="{'text-orange': daynum == 0 && toggleVplan == 1 }">Montag {{ showVplanDay(0) }}</td>
            <td :class="{'text-orange': daynum == 1 && toggleVplan == 1 }">Dienstag {{ showVplanDay(1) }}</td>
            <td :class="{'text-orange': daynum == 2 && toggleVplan == 1 }">Mittwoch {{ showVplanDay(2) }}</td>
            <td :class="{'text-orange': daynum == 3 && toggleVplan == 1 }">Donnerstag {{ showVplanDay(3) }}</td>
            <td :class="{'text-orange': daynum == 4 && toggleVplan == 1 }">Freitag {{ showVplanDay(4) }}</td>
          </tr>
        </thead>
        <tbody>
          <tr v-bind:key="s" v-for="(stunde, s) in stunden" class="">
            <td width="5%">
              {{ stunde +1 }}. Stunde
              <div class="text-small">{{ defaults.zeiten[stunde].begin }} - {{ defaults.zeiten[stunde].ende }}</div>
            </td>
            <td v-bind:key="d" v-for="(day, d) in  days" class="" width="19%">
              <div class="flex-row-nowrap flex-center-center">
                <div v-bind:key="index" v-for="(item, index) in  plan[d][s]" class="si-box  margin-r-s">

                  <StundenplanSlot :item="item" :vplan="showVplan(d, stunde+1, item)" :toggleVplan="toggleVplan" :acl="acl"></StundenplanSlot>
                  
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>


  </div>
</template>

<script>

import StundenplanSlot from './../mixins/StundenplanSlot.vue'

export default {
  name: 'ListComponent',
  components: {
    StundenplanSlot
  },
  data() {
    return {

      isMobile: window.globals.isMobile,

      searchString: '',
      searchResult: false,

      toggleVplan: 1,

      days: [0,1,2,3,4],
      openTab: false

    };
  },
  props: {
    acl: Array,
    list: Array,
    defaults: Array
  },
  computed: {
    plan: function () {
      return this.list.plan;
    },
    stunden: function () {
      return this.defaults.stunden;
    },
    myKlassen: function () {
      if (this.defaults.myKlassen) {
        return this.defaults.myKlassen;
      }
      return false;
    },
    searchResult_first: function () {
      const half = Math.ceil(this.searchResult.length / 2);    
      return this.searchResult.slice(0, half);
    },
    searchResult_second: function () {
      const half = Math.ceil(this.searchResult.length / 2);    
      return this.searchResult.slice(half);
    },
    daynum: function () {
      return parseInt(this.defaults.daynum) -1;
    }
  },
  created: function () {



  },
  methods: {

    handlerToggleVplan() {
      this.toggleVplan = !this.toggleVplan;
    },
    showVplanDay(daynum) {
      
      if (this.toggleVplan == 1 && this.list && this.list.vplan) {
        let found = false;
        this.list.vplan.forEach((item) => {
          if (item.daynum == daynum+1) {
            found = item;
          } 
        });

        if (found) {
          let date = found.date.split('-');
          return date[2]+'.'+date[1]+'.';
        }
      }
      
      return '';
    },
    showVplan(day, stunde, data) {

      if (this.list && this.list.vplan) {
        let found = false;
        this.list.vplan.forEach((item) => {
          console.log(item, data);
          if (item.stunde == stunde
          && item.klasse == data.grade
          && item.user_alt == data.teacher
          && item.daynum == day+1) {
            found = item;
          } 
        });

        if (found) {
          return found;
        }
      }
      return false;

    },
    handlerOpen(item) {

      this.$bus.$emit('page--open', {
        page: 'item',
        item: item
      });

    },

    handlerCloseTab() {
      this.searchResult = false;
      this.openTab = false;
      let node = window.document.getElementById('pageWrapper');
      if (node) {
        node.scrollTo(0,0);
      }
      
    },

    handlerOpenSelect(typ) {

      var that = this;

      if (this.openTab == typ) {
        that.handlerCloseTab();
        return false;
      }

      
      that.handlerCloseTab();
      that.searchResult = [];


      this.openTab = typ;

      if (typ == 'teacher') {
        this.defaults.teachers.forEach((item) => {
          that.searchResult.push( [item.nachname+' '+item.vorname , 'teacher', item.short] );
        });
      }

      if (typ == 'room') {
        this.defaults.rooms.forEach((item) => {
          that.searchResult.push( [item, 'room'] );
        });
      }

      if (typ == 'fach') {
        this.defaults.fach.forEach((item) => {
          that.searchResult.push( [item, 'subject'] );
        });
      }

      if (typ == 'class') {
        for (const key in this.defaults.klassen) {
            //if (that.defaults.klassen.hasOwnProperty(key)) {
              that.defaults.klassen[key].forEach((o) => {
                that.searchResult.push( [o, 'grade'] );
              });
            //}
          }
      }

      


    },

    handlerChangeClass(item) {

      if (!item) {
        return false;
      }
      this.handlerChangePlan([item, 'grade']);

    },
    handlerChangePlan(item) {


      var that = this;

      this.$bus.$emit('item--load', {
        item: item,
        callback: function () {
          //that.searchResult = false;
          that.handlerCloseTab();
          that.searchString = '';
        }
      });

      

    },
    

    handlerSearch() {

      this.handlerCloseTab();
      this.searchResult = [];

      if (this.searchString != '') {
        let split = this.searchString.toLowerCase().split(' ');
        //var search_temp = [];
        
        

        var that = this;

        // Fach
        var search_temp = this.defaults.fach.filter((item) => {
            return split.every(v => item.toLowerCase().includes(v));
        });
        if (search_temp.length > 0) {
          search_temp.forEach((item) => {
            this.searchResult.push( [item, 'subject'] );
          });
        }

        // Raum
        search_temp = this.defaults.rooms.filter((item) => {
            return split.every(v => item.toLowerCase().includes(v));
        });
        if (search_temp.length > 0) {
          search_temp.forEach((item) => {
            this.searchResult.push( [item, 'room'] );
          });
        }

        // Klassen
        if (this.defaults.klassen) {
          for (const key in this.defaults.klassen) {
            //if (this.defaults.klassen.hasOwnProperty(key)) {
                search_temp = this.defaults.klassen[key].filter((item) => {
                  return split.every(v => item.toLowerCase().includes(v));
                });
                if (search_temp.length > 0) {
                  search_temp.forEach((item) => {
                    this.searchResult.push( [item, 'grade'] );
                  });
                }
            //}
          }
        }

        // Teacher
        let teachers = this.defaults.teachers;
        let searchColumns = ['vorname','nachname','short'];
        search_temp = [];
        var search_result = [];
        searchColumns.forEach(function (col) {
          search_temp = teachers.filter((item) => {
            if (item[col]) {
              return split.every(v => item[col].toLowerCase().includes(v));
            }
          });
          if (search_temp.length > 0) {
            search_result = Object.assign(search_result, search_temp);
          }
        });
        if (search_result.length > 0) {
          search_result.forEach((item) => {
            that.searchResult.push( [item.name, 'teacher', item.short] );
          });
        }
        

      }

    }

  }



};
</script>

<style>

.si-table tbody td {
  border-right: 0.1rem solid #e8e8e8;
}

.isMobile .head {
  flex-direction: column !important;
}
.isMobile .head .filter .si-input {
  width: 30%;
  margin-right: 1rem;
}
.isMobile .head .filter .toolbar {
  margin-right: 0;
}


</style>