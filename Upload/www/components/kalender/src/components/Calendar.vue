<template>
    <div class="calendar">
        <div class="calendar-header">
            <button class="btn btn-app chevron-left" @click="subtractWeek">
              <i class="fa fa-arrow-left"></i>Vor
            </button>
            <button @click="gotoToday"
              class="btn btn-app">
              <i class="fa fa-home"></i>Heute
            </button>
            <h3>{{firstDayWeek | moment("Do")}} - {{lastDayWeek | moment("Do MMMM YYYY")}}</h3>
            <button class="btn btn-app chevron-right" @click="addWeek">Weiter
              <i class="fa fa-arrow-right"></i>
            </button>
        </div>

        <table class="">
          <thead>
            <tr>
              <td v-bind:key="index" v-for="(item, index) in daysInWeek"
                :class="{'btn-warning': item[1] == initialDate && month == initialMonth && year == initialYear}">
                {{item[0] | moment("Do dd")}}
              </td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td v-bind:key="i" v-for="(day, i) in daysInWeekFormat" >

                <div v-bind:key="j" v-for="(eintrag, j) in getEintrag(day)" 
                  class="eintrag">

                  <span v-if="eintrag.startTime && eintrag.wholeDay == false">
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
    eintraege: Array
  },
  data(){
    return{
      today: this.$moment(),
      firstDayWeek: false, //this.$moment(),
      days: ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa','So'],

      dayNumber: this.$moment().day()
    }
  },
  created: function () {

    this.firstDayWeek = this.$moment().subtract(this.dayNumber -1, 'days');
    this.changedDate();
    //console.log(this.firstDayWeek.format('dddd, MMMM Do YYYY'));

    var that = this;
    EventBus.$on('calendar--reload', data => {
      that.changedDate();
    });

  },
  computed: {
    getToday: function () {
        var t = this;
        return t.today.format('YYYY-MM-DD');
    },
    year: function () {
        var t = this;
        return t.firstDayWeek.format('Y');
    },
    month: function () {
        var t = this;
        return t.firstDayWeek.format('MMMM');
    },
    daysInWeekFormat: function () {
      var arr = [];
      var foo = this.$moment(this.firstDayWeek);
      for(let i = 0; i < 7; i++) {
        //arr.push( foo.format('YYYY-MM-D') );
        arr.push( [foo, this.$moment(foo).format('YYYY-MM-DD')] );
        foo = this.$moment(foo).add(1, 'day')
      }
      return arr;
    },
    daysInWeek: function () {
      var arr = [];
      var foo = this.$moment(this.firstDayWeek);
      for(let i = 0; i < 7; i++) {
        arr.push( [foo, this.$moment(foo).format('D')] );
        foo = this.$moment(foo).add(1, 'day')
      }
      return arr;
    },
    lastDayWeek: function()  {
      return this.$moment(this.firstDayWeek).add(6, 'days')
    },
    initialDate: function () {
        var t = this;
        return t.today.format('D'); //.get('date');
    },
    initialMonth: function () {
        var t = this;
        return t.today.format('MMMM');
    },
    initialYear: function () {
        var t = this;
        return t.today.format('Y');
    }
  },
  methods: {

    getEintrag: function (day) {
      if (this.eintraege.length <= 0 ) {
        return '';
      }
      var that = this;
      console.log(day, this.eintraege)

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
            'title': eintrag.eintragTitel,
            'start': eintrag.eintragDatumStart,
            'startTime': that.$moment(eintrag.eintragDatumStart, 'YYYY-MM-DD HH:mm:ss', true).format('HH:mm'),
            'end': eintrag.eintragDatumEnde,
            'endTime': that.$moment(eintrag.eintragDatumEnde, 'YYYY-MM-DD HH:mm:ss', true).format('HH:mm'),
            'wholeDay': wholeDay,
            'place': eintrag.eintragOrt,
            'comment': eintrag.eintragKommentar
          });
        }

      });

      ret = ret.sort((a, b) => {
        return moment(a.start).diff(b.start);
      });

      return ret;
    },

    addWeek: function () {
        this.firstDayWeek = this.$moment(this.firstDayWeek).add(1, 'week');
        this.changedDate();
    },
    subtractWeek: function () {
        this.firstDayWeek = this.$moment(this.firstDayWeek).subtract(1, 'week');
        this.changedDate();
    },
    gotoToday: function () {
      this.firstDayWeek = this.$moment().subtract(this.dayNumber -1, 'days');
      this.changedDate();
    },
    addDate: function (day,hour,$event) {
      
      EventBus.$emit('calendar--addDate', {
        day: day,
        hour: hour
      });
      $event.preventDefault();
      return false;
    },
    changedDate: function () {
      EventBus.$emit('calendar--changedDate', {
        von: this.firstDayWeek.unix(),
        bis: this.lastDayWeek.unix()
      });
    }


  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
