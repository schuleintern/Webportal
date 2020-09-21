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
            <h3>{{firstDayOfWeek }} - {{lastDayWeek | dayjs("Do MMMM YYYY")}}</h3>
            <button class="btn btn-app chevron-right" @click="addWeek">Weiter
              <i class="fa fa-arrow-right"></i>
            </button>
        </div>

        <table>
          <thead>
            <tr>
              <td class="hourLabel"></td>
              <td v-bind:key="index" v-for="(item, index) in daysInWeek"
                :class="{'btn-warning': item[1] == initialDate && month == initialMonth && year == initialYear}">
                {{item[0] | moment("Do dd")}}
              </td>
            </tr>
          </thead>
          <tbody>
            <tr v-bind:key="i" v-for="(hour, i) in shoolHours">
              <td class="hourLabel">{{hour}}</td>
              <td v-bind:key="j" v-for="(day, j) in daysInWeekFormat" >

                <div class="info-box bg-green"
                  v-bind:key="key" v-for="(date, key) in dates"
                  v-if="date.ausleiheStunde == hour && date.ausleiheDatum == day[1]">

                    <span class="info-box-text">{{date.ausleiheLehrer}} / {{date.ausleiheKlasse}}</span>
                    <span class="info-box-number">{{date.objektName}} <span v-if="date.sub.length > 0"> ({{date.part}}/{{date.sum}})</span> </span>
                    <span v-if="date.sub.length > 0">{{date.sub}}</span>

                </div>
                <button v-if="isAfterToday(day[1], getToday)" @click="addDate(day,hour,$event)"
                  class="eventAdd btn btn-outline"><i class="fa fa-plus"></i></button>
                  
              </td>
            </tr>
          </tbody>
        </table>

    </div>
</template>


<script>


//import weekday from "dayjs/plugin/weekday";

export default {
  name: 'Calendar',
  props: {
    dates: Array
  },
  data(){
    return{
      today: this.$dayjs(),
      
      //firstDayWeek: false, //this.$dayjs(),
      days: ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa','So'],

      //dayNumber: this.$dayjs().day(),
      shoolHours: [1,2,3,4,5,6,7,8,9,10] // must be numbers!
    }
  },
  created: function () {

    this.$dayjs.locale('de')

    //this.firstDayWeek = this.$dayjs().subtract(this.dayNumber -1, 'days');
    //this.changedDate();
    //console.log(this.firstDayWeek.format('dddd, MMMM Do YYYY'));

    // var that = this;
    // EventBus.$on('calendar--reload', data => {
    //   that.changedDate();
    // });

  },
  computed: {

    firstDayOfWeek: function () {
      return this.$dayjs().startOf('isoWeek');
    },
    lastDayWeek: function () {
      return this.$dayjs().startOf('isoWeek').add(7, 'day');
    }

    // getToday: function () {
    //     var t = this;
    //     return t.today.format('YYYY-MM-D');
    // },
    // year: function () {
    //     var t = this;
    //     return t.firstDayWeek.format('Y');
    // },
    // month: function () {
    //     var t = this;
    //     return t.firstDayWeek.format('MMMM');
    // },
    // daysInWeekFormat: function () {
    //   var arr = [];
    //   var foo = this.$dayjs(this.firstDayWeek);
    //   for(let i = 0; i < 7; i++) {
    //     //arr.push( foo.format('YYYY-MM-D') );
    //     arr.push( [foo, this.$dayjs(foo).format('YYYY-MM-D')] );
    //     foo = this.$dayjs(foo).add(1, 'day')
    //   }
    //   return arr;
    // },
    // daysInWeek: function () {
    //   var arr = [];
    //   var foo = this.$dayjs(this.firstDayWeek);
    //   for(let i = 0; i < 7; i++) {
    //     arr.push( [foo, this.$dayjs(foo).format('D')] );
    //     foo = this.$dayjs(foo).add(1, 'day')
    //   }
    //   return arr;
    // },
    // lastDayWeek: function()  {
    //   return this.$dayjs(this.firstDayWeek).add(6, 'days')
    // },
    // initialDate: function () {
    //     var t = this;
    //     return t.today.format('D'); //.get('date');
    // },
    // initialMonth: function () {
    //     var t = this;
    //     return t.today.format('MMMM');
    // },
    // initialYear: function () {
    //     var t = this;
    //     return t.today.format('Y');
    // }
  },
  methods: {

    // isAfterToday: function (date, today) {
    //   console.log(date);
    //   console.log(this.$dayjs(date, 'YYYY-MM-DD'));
    //   //return this.$dayjs(date, 'YYYY-MM-DD').isSameOrAfter(today);
    // },
    // addWeek: function () {
    //     this.firstDayWeek = this.$dayjs(this.firstDayWeek).add(1, 'week');
    //     this.changedDate();
    // },
    // subtractWeek: function () {
    //     this.firstDayWeek = this.$dayjs(this.firstDayWeek).subtract(1, 'week');
    //     this.changedDate();
    // },
    // gotoToday: function () {
    //   this.firstDayWeek = this.$dayjs().subtract(this.dayNumber -1, 'days');
    //   this.changedDate();
    // },
    // addDate: function (day,hour,$event) {
      
    //   EventBus.$emit('calendar--addDate', {
    //     day: day,
    //     hour: hour
    //   });
    //   $event.preventDefault();
    //   return false;
    // },
    // changedDate: function () {
    //   EventBus.$emit('calendar--changedDate', {
    //     von: this.firstDayWeek.unix(),
    //     bis: this.lastDayWeek.unix()
    //   });
    // }


  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
