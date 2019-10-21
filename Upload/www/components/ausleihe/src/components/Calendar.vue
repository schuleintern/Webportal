<template>
    <div class="calendar">
      Calendar!
        <div class="calendar-header">
            <i class="chevron-left" @click="subtractWeek">left</i>
            <i @click="gotoToday">Heute</i>
            <h3>{{firstDayWeek | moment("Do")}} - {{lastDayWeek | moment("Do MMMM YYYY")}}</h3>
            <i class="chevron-right" @click="addWeek">right</i>
        </div>
        <ul class="days">
            <li></li>
            <li v-bind:key="index" v-for="(day, index) in daysInWeek"
              :class="{'current-day': day == initialDate && month == initialMonth && year == initialYear}">
                <span>{{day | moment("Do dd")}}</span>
           </li>
        </ul>
        <ul class="dates" v-bind:key="i" v-for="(hour, i) in shoolHours">
          <li>{{hour}}</li>
          <li v-bind:key="j" v-for="(day, j) in daysInWeekFormat">
            <button @click="addDate(day,hour,$event)">+</button>

            <div v-bind:key="key" v-for="(date, key) in dates"
              v-if="date.ausleiheStunde == hour && date.ausleiheDatum == day">
              {{date.ausleiheObjektID}}
              <br>
              {{date.ausleiheKlasse}}
              <br>
              {{date.ausleiheLehrer}}
            </div>
            
          </li>
        </ul>
    </div>
</template>


<script>

          

export default {
  name: 'Calendar',
  props: {
    dates: Array
  },
  data(){
    return{
      today: this.$moment(),
      firstDayWeek: false, //this.$moment(),
      days: ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa','So'],

      dayNumber: this.$moment().day(),
      shoolHours: [1,2,3,4,5,6,7,8,9,10] // must be numbers!
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
        arr.push( foo.format('YYYY-MM-D') );
        foo = this.$moment(foo).add(1, 'day')
      }
      return arr;
    },
    daysInWeek: function () {
      var arr = [];
      var foo = this.$moment(this.firstDayWeek);
      for(let i = 0; i < 7; i++) {
        arr.push( foo );
        foo = this.$moment(foo).add(1, 'day')
      }
      return arr;
    },
    lastDayWeek: function()  {
      return this.$moment(this.firstDayWeek).add(6, 'days')
    },
    initialDate: function () {
        var t = this;
        return t.today.get('date');
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
        von: this.firstDayWeek,
        bis: this.lastDayWeek
      });
    }


  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
