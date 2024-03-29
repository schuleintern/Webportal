<template>
  <div class="calendar">

    <div class="calendar-header">
      <button class="btn chevron-left margin-r-xs" @click="prevMonth">
        <i class="fa fa-arrow-left"></i>Zurück
      </button>
      <button @click="gotoToday"
              class="btn">
        <i class="fa fa-home"></i>Heute
      </button>
      <h3>{{ openMonth| moment("MMMM YYYY") }}</h3>
      <button class="btn chevron-right" @click="nextMonth">
        Weiter<i class="fa fa-arrow-right"></i>
      </button>
    </div>

    <table class="">
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
          >{{ day[1] | moment("Do") }}
          </div>

          <div v-bind:key="j" v-for="(eintrag, j) in getEintrag(day)"
               class="eintrag"
               :class="{ 'eintrag-multiple': styleMultipe(eintrag) }"
               v-bind:style="styleEintrag(eintrag, day[1])"
               v-on:click="handlerClickEintrag(eintrag)"
               v-on:mouseover="handlerMouseoverEintrag($event)"
               v-on:mouseleave="handlerMouseleaveEintrag($event)">

            <div class="date">
              <strong>
                    <span v-if="eintrag.startTime != '00:00'">
                      {{ eintrag.startTime }}
                    </span>
                <span v-if="eintrag.endTime != '00:00' && eintrag.wholeDay == false">
                      - {{ eintrag.endTime }}
                    </span>
              </strong>
            </div>

            <div class="title">{{ eintrag.title }}</div>
            <div class="info margin-t-s flex-row text-gey text-small" v-if="eintrag.place || eintrag.comment">
              <div v-if="eintrag.place" class="flex-1"><i class="fas fa-map-marker-alt margin-r-xs"></i>
                {{ eintrag.place }}
              </div>
              <div v-if="eintrag.comment" class="margin-t-s"><i class="fas fa-comment margin-r-s"></i> <span
                  v-html="eintrag.comment">{{ eintrag.comment }}</span></div>
            </div>

          </div>

        </td>
      </tr>
      </tbody>
    </table>

  </div>
</template>


<script>


export default {
  name: 'Calendar',
  props: {
    kalender: Array,
    acl: Object,
    content: Array
  },
  data() {
    return {
      today: this.$moment(),
      openMonth: false,
      openMonthDay: false,
      days: ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'],

      tempDates: []
    }
  },
  created: function () {

    this.openMonth = this.$moment(this.today).date(1);
    this.openMonthDay = this.$moment(this.today).date(1);
    this.gotoToday();

  },
  computed: {

    weekInMonthFormat: function () {
      return 5;
    },

    getToday: function () {
      return this.today.format('YYYY-MM-DD'); //.get('date');
    }

  },
  methods: {

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
      window.EventBus.$emit('eintrag--show-open', {
        eintrag: eintrag
      });
    },
    styleEintrag: function (eintrag, day) {

      var ret = {};
      var that = this;
      this.kalender.forEach(function (kalender) {
        if (parseInt(kalender.kalenderID) == parseInt(eintrag.calenderID)) {

          if (that.styleMultipe(eintrag) == true) {

            if (eintrag.startDay == day) {
              ret = {
                borderLeft: '5px solid ' + kalender.kalenderColor,
                marginLeft: '0.6rem'
              };
            } else if (eintrag.endDay == day) {
              ret = {
                borderRight: '5px solid ' + kalender.kalenderColor,
                marginRight: '0.6rem'
              };
            }
            ret.borderBottom = '2px solid ' + kalender.kalenderColor;


          } else {
            ret = {borderLeft: '5px solid ' + kalender.kalenderColor};
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
      if ( !this.content[monthName] || this.content[monthName].length <= 0) {
        return '';
      }
      if (this.acl && this.acl.rights && parseInt(this.acl.read) != 1) {

        var ret = [];
        var that = this;
        this.content[monthName].forEach((eintrag) => {

          var eintrag_start = new Date(eintrag.eintragDatumStart);
          var eintrag_ende = new Date(eintrag.eintragDatumEnde);

          var date_day = new Date(day[1]);

          if (!eintrag_ende.getTime()) {
            eintrag_ende = new Date(eintrag.eintragDatumStart);
          }

          var wholeDay = false;
          if (eintrag.eintragTimeStart == eintrag.eintragTimeEnde) {
            wholeDay = true;
          }
          let newItem = {
            'id': eintrag.eintragID,
            'title': eintrag.eintragTitel,
            'startDay': eintrag.eintragDatumStart,
            'startTime': eintrag.eintragTimeStart,
            'endDay': eintrag.eintragDatumEnde,
            'endTime': eintrag.eintragTimeEnde,
            'wholeDay': wholeDay,
            'place': eintrag.eintragOrt,
            'comment': eintrag.eintragKommentar,
            'calenderID': eintrag.kalenderID,
            'categoryID': eintrag.eintragKategorieID,
            'createdTime': eintrag.eintragCreatedTime,
            'modifiedTime': eintrag.eintragModifiedTime,
            'createdUserID': eintrag.eintragUserID,
            'createdUserName': eintrag.eintragUserName,
            'repeat_type': eintrag.eintragRepeat
          };

          /*
              DEFAULT
           */
          if (!eintrag.eintragRepeat && eintrag_start <= date_day && date_day <= eintrag_ende) {
            ret.push(newItem);
            //done = true;
          }


          /*
              REPEAT
           */
          if (eintrag.eintragRepeat && +eintrag_start == +date_day) {

            newItem.repeat_root = [that.$moment(eintrag.eintragDatumStart, 'YYYY-MM-DD', true).format('YYYY-MM-DD'),
              that.$moment(eintrag.eintragDatumEnde, 'YYYY-MM-DD', true).format('YYYY-MM-DD')];
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

      if (this.acl.rights.write != 1) {
        return false;
      }

      if (!day) {
        return false;
      }
      window.EventBus.$emit('eintrag--form-open', {
        form: {
          startDay: day
        }
      });
      //$event.preventDefault();
      return false;

    },

    kwInMonth: function (week) {

      return this.$moment(this.openMonth).date(((week - 1) * 7) + 1).isoWeek();

    },
    daysInWeekFormat: function (week) {
      var arr = [];

      var foo = this.$moment(this.openMonth).date(((week - 1) * 7) + 1);
      var diffToMonday = foo.day() - 1;
      foo = foo.subtract(diffToMonday, 'days');

      for (let i = 0; i < 7; i++) {
        arr.push([foo, this.$moment(foo).format('YYYY-MM-DD')]);
        foo = this.$moment(foo).add(1, 'day')
      }
      return arr;
    },

    nextMonth: function () {
      this.openMonth = this.$moment(this.openMonth).add(1, 'months');
    },
    prevMonth: function () {
      this.openMonth = this.$moment(this.openMonth).subtract(1, 'months');
    },
    gotoToday: function () {
      this.openMonth = this.$moment(this.today).date(1);
    }

  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
