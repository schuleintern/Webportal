<template>

  <div class="si-calendar">



    <div v-if="isMobile">

      <div class="si-calendar-header">
        <button class="si-btn" @click="prevMonth"><i class="fa fa-arrow-left"></i></button>
        <button @click="gotoToday" class="si-btn si-btn-light"><i class="fa fa-home"></i></button>
        <div class="title">{{ openMonthFormat }}</div>
        <button class="si-btn" @click="nextMonth"><i class="fa fa-arrow-right"></i></button>
      </div>

      <div class="scrollable-y">
        <div v-bind:key="a" v-for="(week, a) in weekInMonthFormat">
          <div v-bind:key="i" v-for="(day, i) in daysInWeekFormat(week)" class="day"
               :class="{'bg-orange': day[1] == getToday}">
            <div class="dayLabel"
                 v-on:click.self="handlerClickAdd(day[1])">
              {{ showDayLabelMobile(day[1]) }}</div>
            <div v-bind:key="j" v-for="(eintrag, j) in getEintrag(day)" class="eintrag"
                 v-bind:style="styleEintrag(eintrag, day[1])"
                 v-on:click="handlerClickEintrag(eintrag)">
              <DayEintrag :eintrag="eintrag"></DayEintrag>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else>
      <div class="si-calendar-header">
        <button class="si-btn" @click="prevMonth"><i class="fa fa-arrow-left"></i>Zurück</button>
        <button @click="gotoToday" class="si-btn si-btn-light"><i class="fa fa-home"></i>Heute</button>
        <button class="si-btn si-btn-icon si-btn-border" :class="{'si-btn-active': showClock}" @click="handelerShowClock"><i class="fa far fa-clock"></i></button>
        <div class="title">{{ openMonthFormat }}</div>
        <div class="si-btn-multiple blockInline margin-r-m" v-if="calendars.length > 5">
          <button class="si-btn si-btn-icon si-btn-border" @click="handelerSelectAll"><i
              class="fa fas fa-toggle-on"></i></button>
          <button class="si-btn si-btn-icon si-btn-border" @click="handelerDeselectAll"><i
              class="fa fas fa-toggle-off"></i></button>
        </div>
        <button v-if="acl.write == 1" class="si-btn si-btn-icon si-btn-green" v-on:click="handlerAdd"><i class="fa fa-plus"></i></button>
        <button class="si-btn" @click="nextMonth">Weiter <i class="fa fa-arrow-right"></i></button>
      </div>
      <table  class="si-table si-table-style-allLeft">
        <thead>
        <tr>
          <td class="labelKW"></td>
          <td v-bind:key="index" v-for="(item, index) in days" class="day">
            {{ item }}
          </td>
        </tr>
        </thead>
        <tbody>
        <tr v-bind:key="a" v-for="(week, a) in weekInMonthFormat">
          <td class="labelKW"><span class="text-small">KW</span><br>{{ kwInMonth(week) }}
          </td>

          <td v-bind:key="i" v-for="(day, i) in daysInWeekFormat(week)"
              :class="{'bg-orange': day[1] == getToday}"
              class="day"
              v-on:dblclick.self="handlerClickAdd(day[1])">

            <div class="dayLabel"
                 v-on:dblclick.self="handlerClickAdd(day[1])"
            >{{ showDayLabel(day[1]) }}
            </div>

            <div v-bind:key="j" v-for="(eintrag, j) in getEintrag(day)"
                 class="eintrag"
                 :class="{ 'eintrag-multiple': styleMultipe(eintrag) }"
                 v-bind:style="styleEintrag(eintrag, day[1])"
                 v-on:click="handlerClickEintrag(eintrag)"
                 v-on:mouseover="handlerMouseoverEintrag($event)"
                 v-on:mouseleave="handlerMouseleaveEintrag($event)">
              <DayEintrag :eintrag="eintrag" :showClock="showClock"></DayEintrag>
            </div>

          </td>
        </tr>
        </tbody>
      </table>
    </div>



  </div>

</template>

<script>

//import '@fullcalendar/core/vdom' // solves problem with Vite
//import FullCalendar from '@fullcalendar/vue'
//import dayGridPlugin from '@fullcalendar/daygrid'
//import interactionPlugin from '@fullcalendar/interaction'

import DayEintrag from './../mixins/DayEintrag.vue'

export default {
  name: 'CalendarView',
  components: {
    DayEintrag
  },
  data() {
    return {
      isMobile: window.globals.isMobile,
      calendarOptions: {
        //plugins: [ dayGridPlugin, interactionPlugin ],
        initialView: 'dayGridMonth'
      },
      showClock: false,
      selected: [],

      today: this.$dayjs(),
      openMonth: false,
      openMonthDay: false,
      days: ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So']
    };
  },
  props: {

    events: Array,
    calendars: Array,
    acl: Object

  },
  created: function () {

    //console.log(this.events);

    this.openMonth = this.$dayjs(this.today).date(1);
    this.openMonthDay = this.$dayjs(this.today).date(1);
    this.gotoToday();

  },
  computed: {
    openMonthFormat() {
      return this.openMonth.locale('de').format("MMMM YYYY")
    },
    weekInMonthFormat: function () {
      return 5;
    },
    getToday: function () {
      return this.today.format('YYYY-MM-DD'); //.get('date');
    }
  },
  methods: {
    handlerAdd() {
      if (this.acl.write != 1) {
        return false;
      }
      this.$bus.$emit('event-form--open', {
        form: {}
      });
    },
    handelerDeselectAll() {
      this.selected = [];
      this.$bus.$emit('kalenders--preSelected', {
        selected: this.selected
      });
    },
    handelerSelectAll() {
      this.selected = [];
      this.calendars.forEach((o) => {
        this.selected.push(parseInt(o.id));
      });
      this.$bus.$emit('kalenders--preSelected', {
        selected: this.selected
      });
    },
    handelerShowClock() {
      this.showClock = !this.showClock;
    },
    showDayLabel(day) {
      return this.$dayjs(day).format("D") + '.';
    },
    showDayLabelMobile(day) {
      return this.$dayjs(day).locale('de').format("DD.MM. dddd");
    },
    handlerMouseoverEintrag: function (e) {
      e.target.classList.add('open');
    },
    handlerMouseleaveEintrag: function (e) {
      e.target.classList.remove('open');
    },
    handlerClickEintrag: function (eintrag) {
      if (!eintrag) {
        return false;
      }
      if (this.acl.read != 1) {
        return false;
      }
      this.$bus.$emit('event-item--open', {
        item: eintrag
      });

      //$event.preventDefault();
      return false;
    },
    styleEintrag: function (eintrag, day) {
      var ret = {};
      var that = this;
      this.calendars.forEach(function (calendars) {
        if (parseInt(calendars.id) == parseInt(eintrag.calenderID)) {

          if (that.styleMultipe(eintrag) == true) {

            if (eintrag.startDay == day) {
              ret = {
                borderLeft: '5px solid ' + calendars.color,
                marginLeft: '0.6rem'
              };
            } else if (eintrag.endDay == day) {
              ret = {
                borderRight: '5px solid ' + calendars.color,
                marginRight: '0.6rem'
              };
            }
            ret.borderBottom = '2px solid ' + calendars.color;
          } else {
            ret = {borderLeft: '5px solid ' + calendars.color};
          }
        }
      });

      return ret;
    },
    styleMultipe: function (eintrag) {
      if (eintrag.endDay == '0000-00-00') {
        return false;
      } else if (eintrag.endDay == eintrag.startDay) {
        return false;
      }
      return true;
    },
    getEintrag: function (day) {

      let monthName = day[0].format('MM-YYYY');
      if (!this.events[monthName] || this.events[monthName].length <= 0) {
        return '';
      }
      if (this.acl && this.acl.read && parseInt(this.acl.read) == 1) {

        var ret = [];
        var that = this;




        this.events[monthName].forEach((eintrag) => {


          var eintrag_start = new Date(eintrag.dateStart);
          var eintrag_ende = new Date(eintrag.dateEnd);

          var date_day = new Date(day[1]);

          if (!eintrag_ende.getTime()) {
            eintrag_ende = new Date(eintrag.dateStart);
          }

          var wholeDay = false;
          if (eintrag.timeStart == eintrag.timeEnd) {
            wholeDay = true;
          }
          let newItem = {
            'id': eintrag.id,
            'title': eintrag.title,
            'startDay': eintrag.dateStart,
            'startTime': eintrag.timeStart,
            'endDay': eintrag.dateEnd,
            'endTime': eintrag.timeEnd,
            'wholeDay': wholeDay,
            'place': eintrag.place,
            'comment': eintrag.comment,
            'calenderID': eintrag.calenderID,
            'categoryID': 0,
            'createdTime': eintrag.createdTime,
            'modifiedTime': eintrag.modifiedTime,
            'createdUserID': eintrag.user_id,
            'createdUserName': eintrag.user.name,
            'repeat_type': eintrag.repeat_type,
            'stunde': eintrag.stunde,
            'typ': eintrag.typ,
            'art': eintrag.art,
            'fach': eintrag.fach,
            'teacher': eintrag.teacher
          };


          //console.log(eintrag);

          /*
              DEFAULT
           */
          if (!eintrag.repeat_type && eintrag_start <= date_day && date_day <= eintrag_ende) {
            ret.push(newItem);
            //done = true;
          }


          /*
              REPEAT
           */
          //if (eintrag.repeat_type && eintrag_start <= date_day && eintrag_start >= date_day  ) {
          if (eintrag.repeat_type && +eintrag_start == +date_day) {

            newItem.repeat_root = [that.$dayjs(eintrag.dateStart, 'YYYY-MM-DD', true).format('YYYY-MM-DD'),
              that.$dayjs(eintrag.dateEnd, 'YYYY-MM-DD', true).format('YYYY-MM-DD')];
            newItem.endDay = '0000-00-00';

            ret.push(newItem);
          }
        });


        ret = ret.sort((a, b) => {
          return a.startTime > b.startTime;
        });


        return ret;
      }

    },

    handlerClickAdd: function (day) {

      if (this.acl.write != 1) {
        return false;
      }

      if (!day) {
        return false;
      }
      this.$bus.$emit('event-form--open', {
        form: {
          startDay: day
        }
      });
      //$event.preventDefault();
      return false;

    },

    kwInMonth: function (week) {
      return this.$dayjs(this.openMonth).date(((week - 1) * 7) + 2).isoWeek();
    },
    daysInWeekFormat: function (week) {
      var arr = [];

      var foo = this.$dayjs(this.openMonth).date(((week - 1) * 7) + 1);
      var diffToMonday = foo.day() - 1;
      foo = foo.subtract(diffToMonday, 'days');

      for (let i = 0; i < 7; i++) {
        arr.push([foo, this.$dayjs(foo).format('YYYY-MM-DD')]);
        foo = this.$dayjs(foo).add(1, 'day')
      }
      return arr;
    },

    nextMonth: function () {
      this.openMonth = this.$dayjs(this.openMonth).add(1, 'months');
    },
    prevMonth: function () {
      this.openMonth = this.$dayjs(this.openMonth).subtract(1, 'months');
    },
    gotoToday: function () {
      this.openMonth = this.$dayjs(this.today).date(1);
    }


  }


};
</script>

<style>


</style>