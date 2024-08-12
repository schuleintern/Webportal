<template>
  <div class="">
    <div class="flex-row head">

      <div class="flex-row filter" v-if="defaults.showFilter === true">
        <input type="search" v-model="searchString" @keyup="handlerSearch" placeholder="Suche..."
               class="si-input margin-r-l width-12rem"/>
        <div class="toolbar si-btn-multiple margin-r-m ">
          <button class="si-btn si-btn-icon si-btn-light margin-r-xs" :class="{'si-btn-active': 'teacher' == openTab }"
                  @click="handlerOpenSelect('teacher')"><i class="fa fa-user"></i></button>
          <button class="si-btn si-btn-icon si-btn-light margin-r-xs" :class="{'si-btn-active': 'class' == openTab }"
                  @click="handlerOpenSelect('class')"><i class="fa fa-users"></i></button>
          <button class="si-btn si-btn-icon si-btn-light margin-r-xs" :class="{'si-btn-active': 'fach' == openTab }"
                  @click="handlerOpenSelect('fach')"><i class="fa fa-flask"></i></button>
          <button class="si-btn si-btn-icon si-btn-light margin-r-xs" :class="{'si-btn-active': 'room' == openTab }"
                  @click="handlerOpenSelect('room')"><i class="fa fa-door-open"></i></button>
        </div>
      </div>

      <div class="si-btn-multiple margin-r-m mobile-margin-t-s" v-if="myKlassen.length > 1">
        <button class="si-btn margin-r-xs" :class="{'si-btn-active': item == list.active }"
                v-bind:key="index" v-for="(item, index) in  myKlassen"
                @click="handlerChangeClass(item)">{{ item }}
        </button>
      </div>

      <div class="mobile-margin-t-s" v-if="plan">
        <span v-if="showVplanBtn" >
          <button v-if="toggleVplan == 1" class="si-btn si-btn-icon si-btn-toggle-on" v-on:click="handlerToggleVplan">
            <i class="fa fas fa-toggle-on"></i> <i class="fa fa-retweet"></i></button>
          <button v-else class="si-btn si-btn-icon si-btn-toggle-off" v-on:click="handlerToggleVplan">
            <i class="fa fas fa-toggle-off"></i> <i class="fa fa-retweet"></i></button>
        </span>
        <!--
        <a class="si-btn si-btn-border si-btn-icon margin-l-m" target="_blank"
           :href="'index.php?page=ext_stundenplan&view=default&task=print'+activePlanParams"><i class="fa fa-print"></i></a>
           -->
        <button v-if="showPrintBtn" class="si-btn si-btn-border si-btn-icon margin-l-m" @click="handlerPrint"><i class="fa fa-print"></i></button>
      </div>

      <div class="title flex-1 flex-row flex-end padding-r-l">
        <h3 id="stundenplanHeadTitle">{{ list.title }}</h3>
      </div>

    </div>

    <div v-if="searchResult">

      <div class="columns-6 mobile-colums-3 padding-t-l">

        <button v-bind:key="index" v-for="(item, index) in  searchResult" class="si-btn si-btn-border width-100p"
                @click="handlerChangePlan(item)">
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

      <table id="stundenplanTable" class="si-table si-table-style-firstLeft" v-if="plan && plan.length >= 1">

        <thead>
        <tr class="rowTableHead">
          <td ></td>
          <td :class="{'text-orange': daynum == 0 && toggleVplan == 1 }">Montag {{ showVplanDay(0) }}</td>
          <td :class="{'text-orange': daynum == 1 && toggleVplan == 1 }">Dienstag {{ showVplanDay(1) }}</td>
          <td :class="{'text-orange': daynum == 2 && toggleVplan == 1 }">Mittwoch {{ showVplanDay(2) }}</td>
          <td :class="{'text-orange': daynum == 3 && toggleVplan == 1 }">Donnerstag {{ showVplanDay(3) }}</td>
          <td :class="{'text-orange': daynum == 4 && toggleVplan == 1 }">Freitag {{ showVplanDay(4) }}</td>
        </tr>
        </thead>
        <tbody>
        <tr v-bind:key="s" v-for="(stunde, s) in stunden" class="rowTable">
          <td width="10%" class="">
            {{ stunde + 1 }}. {{stundeLabel}}
            <div class="text-small">{{ defaults.zeiten[stunde].begin }} - {{ defaults.zeiten[stunde].ende }}</div>
          </td>
          <td v-bind:key="d" v-for="(day, d) in  days" class="tdTable" width="19%">
            <div class="flex-row-nowrap flex-center-center">
              <div v-bind:key="index" v-for="(item, index) in  plan[d][s]" class="si-box  margin-r-s">

                <StundenplanSlot :item="item" :vplan="showVplan(d, stunde+1, item)" :toggleVplan="toggleVplan"
                                 :acl="acl"></StundenplanSlot>

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
import {jsPDF} from "jspdf";
import html2canvas from "html2canvas";

import StundenplanSlot from './../mixins/StundenplanSlot.vue'

export default {
  name: 'ListComponent',
  components: {
    StundenplanSlot
  },
  data() {
    return {
      showVplanBtn: window.globals.showVplanBtn,
      showPrintBtn: window.globals.showPrintBtn,
      stundeLabel: window.globals.stundeLabel || 'Stunde',
      isMobile: window.globals.isMobile,
      printLogo: window.globals.printLogo,
      printSystem: window.globals.printSystem,
      printDate: window.globals.printDate,

      searchString: '',
      searchResult: false,

      toggleVplan: 1,

      days: [0, 1, 2, 3, 4],
      openTab: false,

      activePlan: false

    };
  },
  props: {
    acl: Array,
    list: Array,
    defaults: Array
  },
  computed: {
    activePlanParams: function () {

      if (this.activePlan && this.activePlan[1] == 'teacher') {
        return '&key=' + this.activePlan[1] + '&value=' + this.activePlan[2];
      }
      return '&key=' + this.activePlan[1] + '&value=' + this.activePlan[0];

    },
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
      return parseInt(this.defaults.daynum) - 1;
    }
  },
  created: function () {


  },
  methods: {

    handlerPrint() {


      window.html2canvas = html2canvas;

      // Default export is a4 paper, portrait, using millimeters for units
      var doc = new jsPDF(
          'l', 'mm', 'a4'
      );

      let html = document.getElementById('stundenplanTable');

      let tableTemp = document.createElement('table');
      //tableTemp.style.margin = '0';
      //tableTemp.style.margintop = '1rem';
      tableTemp.classList = html.classList;
      NodeList.prototype.forEach = Array.prototype.forEach;
      html.childNodes.forEach(function(item){
        tableTemp.appendChild(item.cloneNode(true));
      });


      var childDiv = tableTemp.getElementsByClassName('si-box');
      for (var i = 0; i < childDiv.length; i++) {
        childDiv[i].style.marginTop = "0";
        childDiv[i].style.marginBottom = "0";
        childDiv[i].style.padding = "0";
        childDiv[i].style.paddingLeft = "1mm";
        childDiv[i].style.paddingTop = "1mm";
        childDiv[i].style.paddingRight = "1mm";
      }


      var tds = tableTemp.getElementsByClassName('tdTable');
      for (var m = 0; m < tds.length; m++) {

        tds[m].style.padding = '0';
        tds[m].style.margin = '0';
        tds[m].style.height = '1rem';

        var boxs = tds[m].getElementsByClassName('si-box');

        for (var n = 0; n < boxs.length; n++) {

          boxs[n].style.border = 'none';
          if (boxs.length > 1) {
            boxs[n].style.borderRight = '1px solid #ccc';
          }
        }

      }


      var rows = tableTemp.getElementsByClassName('rowTable');
      const rowHeight = 145 / rows.length;

      for (var j = 0; j < rows.length; j++) {

        if (rows[j]) {
          rows[j].style.padding = '0';
          rows[j].style.margin = '0';
          rows[j].style.height = rowHeight+'mm';
        }
      }

      var rowsHead = tableTemp.getElementsByClassName('rowTableHead');
      for (var j2 = 0; j2 < rowsHead.length; j2++) {

        if (rowsHead[j2]) {
          rowsHead[j2].style.padding = '0';
          rowsHead[j2].style.margin = '0';
          rowsHead[j2].style.height = '3rem';


          var rowsHeadTds = rowsHead[j2].getElementsByTagName('td');
          if (rowsHeadTds) {
            for (var j3 = 0; j3 < rowsHeadTds.length; j3++) {
              rowsHeadTds[j3].style.padding = '0';
              rowsHeadTds[j3].classList.remove('text-orange');
            }
          }
        }

      }

      tableTemp.style.margin = 0;
      tableTemp.style.marginTop = '4mm';

      let htmlTemp = document.createElement('div');
      htmlTemp.style.width = '250mm';
      htmlTemp.style.height = '170mm'; //210mm
      htmlTemp.style.fontSize = '80%';
      htmlTemp.style.marginTop = '7mm';
      htmlTemp.style.marginLeft = '10mm';
      htmlTemp.style.marginRight = '10mm';

      let imgHead = document.createElement('img');
      imgHead.src = this.printLogo;
      imgHead.style.textAlign = 'right';
      imgHead.style.width = '10mm';
      imgHead.style.height = '10mm';
      imgHead.style.display = 'inline';
      imgHead.style.top = '-2mm';
      imgHead.style.position = 'relative';
      htmlTemp.appendChild(imgHead);


      let htmlHead = document.createElement('h3');
      let nodeTitle = document.getElementById('stundenplanHeadTitle');
      htmlHead.innerText = nodeTitle.innerText;
      htmlHead.style.textAlign = 'left';
      htmlHead.style.padding = '0';
      htmlHead.style.margin = '0';
      htmlHead.style.paddingRight = '5rem';
      htmlHead.style.paddingLeft = '2rem';
      htmlHead.style.display = 'inline-block';
      htmlHead.style.width = '200mm';
      htmlTemp.appendChild(htmlHead);


      htmlTemp.style.marginLeft = '3rem';
      htmlTemp.appendChild(tableTemp);

      let htmlFooter = document.createElement('div');
      htmlFooter.innerText = this.printSystem+' - '+this.printDate;
      htmlFooter.style.fontSize = '7pt';
      htmlFooter.style.width = '100%';
      htmlFooter.style.textAlign = 'center';
      htmlFooter.style.paddingTop = '2mm';
      htmlFooter.style.color = '#ccc';
      htmlTemp.appendChild(htmlFooter);

      // for Debug:
      //html.parentNode.appendChild(htmlTemp);

      doc.html(htmlTemp, {
        callback: function (doc) {
          doc.save("Stundenplan-A4.pdf");
        },
        x: 0,
        y: 0,
        width: 297,
        windowWidth: 1024
      });
      //doc.save("a4.pdf");

    },
    handlerToggleVplan() {
      this.toggleVplan = !this.toggleVplan;
    },
    showVplanDay(daynum) {

      if (this.toggleVplan == 1 && this.list && this.list.vplan) {
        let found = false;
        this.list.vplan.forEach((item) => {
          if (item.daynum == daynum + 1) {
            found = item;
          }
        });

        if (found) {
          let date = found.date.split('-');
          return date[2] + '.' + date[1] + '.';
        }
      }

      return '';
    },
    showVplan(day, stunde, data) {

      if (this.list && this.list.vplan) {
        let found = false;
        this.list.vplan.forEach((item) => {
          //console.log(item, data);
          if (item.stunde == stunde
              && item.klasse == data.grade
              && item.user_alt == data.teacher
              && item.daynum == day + 1) {
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
        node.scrollTo(0, 0);
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
          that.searchResult.push([item.nachname + ' ' + item.vorname, 'teacher', item.short]);
        });
      }

      if (typ == 'room') {
        this.defaults.rooms.forEach((item) => {
          that.searchResult.push([item, 'room']);
        });
      }

      if (typ == 'fach') {
        this.defaults.fach.forEach((item) => {
          that.searchResult.push([item, 'subject']);
        });
      }

      if (typ == 'class') {
        for (const key in this.defaults.klassen) {
          //if (that.defaults.klassen.hasOwnProperty(key)) {
          if (that.defaults.klassen[key] && typeof that.defaults.klassen[key] == 'object') {
            that.defaults.klassen[key].forEach((o) => {
              that.searchResult.push([o, 'grade']);
            });
          } else {
            that.searchResult.push([that.defaults.klassen[key], 'grade']);
          }

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

      this.activePlan = item;

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
            this.searchResult.push([item, 'subject']);
          });
        }

        // Raum
        search_temp = this.defaults.rooms.filter((item) => {
          return split.every(v => item.toLowerCase().includes(v));
        });
        if (search_temp.length > 0) {
          search_temp.forEach((item) => {
            this.searchResult.push([item, 'room']);
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
                this.searchResult.push([item, 'grade']);
              });
            }
            //}
          }
        }

        // Teacher
        let teachers = this.defaults.teachers;
        let searchColumns = ['vorname', 'nachname', 'short'];
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
            that.searchResult.push([item.name, 'teacher', item.short]);
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