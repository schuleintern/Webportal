<template>
  <div>
    <div class="calendar">
        <div class="calendar-header">
            <button class="btn btn-app chevron-left" @click="subtractWeek">
              <i class="fa fa-arrow-left"></i>Vor
            </button>
            <button @click="gotoToday"
              class="btn btn-app">
              <i class="fa fa-home"></i>Heute
            </button>
            <h3>{{ $date(firstDayOfWeek).format("DD.") }} - {{ $date(lastDayOfWeek).format("DD. MMMM YYYY") }}</h3>
            <button class="btn btn-app chevron-right" @click="addWeek">
              <i class="fa fa-arrow-right"></i>Weiter
            </button>
        </div>

        <table class="table_1 noPadding">
          <thead>
            <tr>
              <td v-bind:key="j" v-for="(day, j) in daysInWeek"
               :class="{ 'bg-orange': isToday(day) == true}">
                {{ $date(day).format('DD. dd') }}
              </td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td v-bind:key="i" v-for="(day, i) in daysInWeek" >
                
                <div v-bind:key="j" v-for="(item, j) in getEintrag(day)"
                  class="padding-t-m" >
                  
                  <div v-bind:key="k" v-for="(gruppe, k) in item.gruppen"
                    class="gruppe padding-s box_1 margin-l-m margin-r-m"
                    
                    v-on:click="openEintrag(gruppe)">

                    <div class="text-big-2">
                      <span v-if="gruppe.gruppe.farbe" style="width:2rem; height:2rem; display:inline-block;" v-bind:style="{ backgroundColor: gruppe.gruppe.farbe }" class="border-radius"  ></span>
                      {{gruppe.gruppe.name}}</div>
                    <div class="padding-t-s padding-b-s">
                      <span v-if="gruppe.schueler.length > 0 && gruppe.gruppe.absenz_anz == 0" class=" text-grau bg-white border-radius padding-t-xs padding-b-xs padding-l-s padding-r-s margin-r-m text-bold"
                        v-bind:style="{ color: gruppe.gruppe.farbe }">
                        <i class="fa fa-child margin-r-m"></i>{{gruppe.schueler.length}}
                      </span>
                      <span v-show="gruppe.gruppe.absenz_anz" class="bg-white  text-grau border-radius padding-t-xs padding-b-xs padding-l-s padding-r-s margin-r-m text-bold"
                        v-bind:style="{ color: gruppe.gruppe.farbe }">
                        <i class="fa fa-child margin-r-m"></i>{{gruppe.schueler.length-gruppe.gruppe.absenz_anz}} <span class="text-small">({{gruppe.schueler.length}})</span>  
                      </span>
                      <span v-show="gruppe.gruppe.absenz_anz" class="bg-white  text-red border-radius padding-t-xs padding-b-xs padding-l-s padding-r-s margin-r-m text-bold">
                        <i class="fa fa-bed margin-r-m"></i>{{gruppe.gruppe.absenz_anz}}
                      </span>
                      <div class="flex-1 margin-t-m text-grey" v-show="gruppe.gruppe.raum"><i class="fas fa-map-marker-alt"></i> {{gruppe.gruppe.raum}}</div>
                    </div>

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

export default {
  name: 'Calendar',
  props: {
    list: Array,
    acl: Object
  },
  data(){
    return{

      today: this.$date(),
      thisWeek: false,

      //prevDays: globals.prevDays

    }
  },
  created: function () {

    this.thisWeek = this.today;
    this.changedDate();

    //this.prevDays = parseInt( this.prevDays );

    var that = this;
    EventBus.$on('calender--reload', data => {
      that.changedDate();
    });
  },
  computed: {

    firstDayOfWeek: function () {
      return this.thisWeek.startOf('week');
    },
    lastDayOfWeek: function () {
      return this.thisWeek.endOf('week');
    },
    daysInWeek: function () {
      var arr = [];
      var foo = this.firstDayOfWeek;
      for(let i = 0; i < 7; i++) {
        if ( globals.showDays[ foo.format('dd') ] == 1 ) {
          arr.push( [ foo ] );
        }
        foo = foo.add(1,'day');
      }
      return arr;
    },
    daysInWeekFull: function () {
      var arr = [];
      var foo = this.firstDayOfWeek;
      for(let i = 0; i < 7; i++) {
        if ( globals.showDays[ foo.format('dd') ] == 1 ) {
          arr.push( [ foo.format('YYYY-MM-DD'), foo.format('dd') ] );
        }
        foo = foo.add(1,'day');
      }
      return arr;
    }

  },
  methods: {

    openEintrag: function (item) {
      EventBus.$emit('item--open', {
        item: item
      });
    },
    showBuchenBtn: function (day) {
      // var prev = this.today.add( this.prevDays , 'day');
      // if ( prev.isBefore(day) ) {
      //   return true;
      // }
      return false;
    },
    isToday: function (day) {
      if ( this.today.isSame( day, 'day' ) ) {
        return true;
      }
      return false;
    },
    subtractWeek: function () {
      this.thisWeek = this.thisWeek.subtract(1, 'week');
      this.changedDate();
    },
    addWeek: function () {
      this.thisWeek = this.thisWeek.add(1, 'week');
      this.changedDate();
    },
    gotoToday: function () {
      this.thisWeek = this.today;
      this.changedDate();
    },
    changedDate: function () {
      EventBus.$emit('calendar--changedDate', {
        von: this.firstDayOfWeek.unix(),
        bis: this.lastDayOfWeek.unix(),
        days: this.daysInWeekFull
      });
    },

    openForm: function (day) {
      EventBus.$emit('form--open', {
        item: {date: day}
      });
    },

    getEintrag: function (day) {
      if (this.list.length <= 0 ) {
        return '';
      }
      var day = this.$date(day).format('YYYY-MM-DD');
      var ret = [];
      this.list.forEach(function (item) {
        if (day == item[0] ) {
          ret.push(item);
        }
      });
      return ret;
    },

    openEintrag: function (item) {
      EventBus.$emit('item--open', {
        item: item
      });
    },

    orderEintrag: function (item) {
      if (!item.id) {
        return false;
      }
      EventBus.$emit('item--order', {
        item: item
      });
    }
  }
}
</script>
