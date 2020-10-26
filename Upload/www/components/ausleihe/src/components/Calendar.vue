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
            <button class="btn btn-app chevron-right" @click="addWeek">
              <i class="fa fa-arrow-right"></i>Weiter
            </button>
        </div>

        <table class="table_1">
          <thead>
            <tr>
              <td class="hourLabel"></td>
              <td v-bind:key="index" v-for="(item, index) in daysInWeek"
                :class="{'btn-warning': item[1] == initialDate && month == initialMonth && year == initialYear}">
                {{item[0] | moment("Do dd")}}
              </td>
            </tr>
          </thead>
          <tbody class="oddEven">
            <tr v-bind:key="i" v-for="(hour, i) in shoolHours">
              <td class="hourLabel">{{hour}}</td>
              <td v-bind:key="j" v-for="(day, j) in daysInWeekFormat"
                class="">
                
                <div class="box_1"
                  v-bind:key="key" v-for="(date, key) in dates"
                  v-if="showEintrag(date.ausleiheStunde, hour, date.ausleiheDatum, day[1]) == true" >

                    <div class="">{{date.ausleiheLehrer}} / {{date.ausleiheKlasse}}</div>
                    <div class="text-bold">{{date.objektName}} <span v-if="date.sub.length > 0"> ({{date.part}}/{{date.sum}})</span> </div>
                    <div v-if="date.sub.length > 0">{{date.sub}}</div>

                </div>
                <button v-if="isAfterToday(day[1], getToday)" v-on:click="addDate(day,hour,$event)"
                  class="eventAdd btn btn-opacity noText width-100p"><i class="fa fa-plus"></i></button>
                  
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
    
    getToday: function () {
        var t = this;
        return t.today.format('YYYY-MM-D');
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
        arr.push( [foo, this.$moment(foo).format('YYYY-MM-D')] );
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

    showEintrag(ausleiheStunde, hour, ausleiheDatum, day) {
      if (ausleiheStunde == hour
        && this.$moment(ausleiheDatum).format('YYYY-MM-D') == this.$moment(day).format('YYYY-MM-D') ) {
        return true;
      }
      return false;
    },
    isAfterToday: function (date, today) {
      var date_js = new Date(date.replace(/-/g, "/"));
      var today_js = new Date(today.replace(/-/g, "/"));
      if ( date_js >= today_js) {
        return true;
      }
      return false;
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
