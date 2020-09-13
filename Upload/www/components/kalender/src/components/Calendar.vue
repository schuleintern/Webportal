<template>
    <div class="calendar">

      <div class="calendar-header">
          <button class="btn chevron-left margin-r-xs" @click="prevMonth">
            <i class="fa fa-arrow-left"></i>Vor
          </button>
          <button @click="gotoToday"
            class="btn">
            <i class="fa fa-home"></i>Heute
          </button>
          <h3>{{openMonth| moment("MMMM YYYY")}}</h3>
          <button class="btn chevron-right" @click="nextMonth">
             Weiter<i class="fa fa-arrow-right"></i>
          </button>
      </div>

      <table class="">
        <thead>
          <tr>
            <td v-bind:key="index" v-for="(item, index) in days">
              {{item}}
            </td>
          </tr>
        </thead>
        <tbody>
          <tr v-bind:key="a" v-for="(week, a) in weekInMonthFormat">
            <td v-bind:key="i" v-for="(day, i) in daysInWeekFormat(week)"
              :class="{'btn-warning': day[1] == getToday}"
              v-on:dblclick.self="handlerClickAdd(day[1])" >

              {{day[1] | moment("Do")}}

              <div v-bind:key="j" v-for="(eintrag, j) in getEintrag(day)" 
                class="eintrag"
                v-bind:style="styleEintrag(eintrag)"
                v-on:click="handlerClickEintrag(eintrag)">

                <span v-if="eintrag.startTime != '00:00'">
                  {{eintrag.startTime}}
                </span>
                <span v-if="eintrag.endTime && eintrag.wholeDay == false">
                  - {{eintrag.endTime}}
                </span>
                <span v-if="eintrag.wholeDay == false"> Uhr</span>
                <div>{{eintrag.title}}</div>
                <div>
                  <span v-if="eintrag.place">{{eintrag.place}}</span>
                  <span v-if="eintrag.comment">!!!</span>
                </div>

              </div>

              <br/>

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
    eintraege: Array,
    kalender: Array
  },
  data(){
    return{
      today: this.$moment(),
      openMonth: false,
      openMonthDay: false,
      days: ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa','So']
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

    handlerClickEintrag: function (eintrag) {
      if (!eintrag) {
        return false;
      }
      EventBus.$emit('eintrag--show-open', {
        eintrag: eintrag
      });
    },
    styleEintrag: function (eintrag) {

      var ret = false;
      this.kalender.forEach(function (kalender) {
        if ( parseInt(kalender.kalenderID) == parseInt(eintrag.calenderID) ) {
          ret = { borderLeft: '5px solid '+kalender.kalenderColor };
        }
      });
      return ret;
    },
    getEintrag: function (day) {
      if (this.eintraege.length <= 0 ) {
        return '';
      }
      var that = this;
      //console.log(day, this.eintraege)

      var ret = [];

      this.eintraege.forEach(function (eintrag) {

        if( that.$moment(eintrag.eintragDatumStart, 'YYYY-MM-DD HH:mm:ss', true)
          .isSameOrAfter(day[1]+' 00:00:00') 
          && that.$moment(eintrag.eintragDatumEnde, 'YYYY-MM-DD HH:mm:ss', true)
          .isBefore(day[1]+' 23:59:59')) {
          
          var wholeDay = false;
          if (eintrag.eintragDatumStart == eintrag.eintragDatumEnde) {
            wholeDay = true;
          }
          ret.push({
            'id': eintrag.eintragID,
            'title': eintrag.eintragTitel,
            'day': that.$moment(eintrag.eintragDatumStart, 'YYYY-MM-DD HH:mm:ss', true).format('YYYY-MM-DD'),
            'start': eintrag.eintragDatumStart,
            'startTime': that.$moment(eintrag.eintragDatumStart, 'YYYY-MM-DD HH:mm:ss', true).format('HH:mm'),
            'end': eintrag.eintragDatumEnde,
            'endTime': that.$moment(eintrag.eintragDatumEnde, 'YYYY-MM-DD HH:mm:ss', true).format('HH:mm'),
            'wholeDay': wholeDay,
            'place': eintrag.eintragOrt,
            'comment': eintrag.eintragKommentar,
            'calenderID': eintrag.kalenderID,
            'categoryID': eintrag.eintragKategorieID,
            'createdTime': eintrag.eintragCreatedTime,
            'modifiedTime': eintrag.eintragModifiedTime,
            'createdUserID': eintrag.eintragUserID,
            'createdUserName': eintrag.eintragUserName
          });
          //console.log(ret);
        }

      });

      ret = ret.sort((a, b) => {
        return moment(a.start).diff(b.start);
      });

      return ret;
    },

    handlerClickAdd: function (day) {

      if (!day) {
        return false;
      }
      EventBus.$emit('eintrag--form-open', {
        form: {
          day: day
        }
      });
      //$event.preventDefault();
      return false;

    },

    daysInWeekFormat: function (week) {
      var arr = [];

      var foo = this.$moment(this.openMonth).date(( (week-1) * 7)+1);
      var diffToMonday = foo.day() - 1;
      foo = foo.subtract(diffToMonday, 'days');

      for(let i = 0; i < 7; i++) {
        arr.push( [foo, this.$moment(foo).format('YYYY-MM-DD')] );
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
