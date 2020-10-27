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

        <table class="table_1">
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
              <td v-bind:key="j" v-for="(day, j) in daysInWeek" >
                
                <div v-bind:key="j" v-for="(item, j) in getEintrag(day)" 
                  class="eintrag" v-on:click="openEintrag(item)"
                  v-if="acl.rights.read == 1">
                  <div class="title margin-b-s">{{item.title}}</div>
                  <div class="text-green margin-b-s">
                    <div v-if="item.vegetarisch == 1"><i class="fas fa-seedling width-2rem"></i> Vegetarisch</div>
                    <div v-if="item.vegan == 1"><i class="fas fa-leaf width-2rem"></i> Vegan</div>
                    <div v-if="item.laktosefrei == 1"><i class="fas fa-wine-bottle width-2rem"></i> Laktosefrei</div>
                    <div v-if="item.glutenfrei == 1"><i class="fab fa-pagelines width-2rem"></i> Glutenfrei</div>
                    <div v-if="item.bio == 1"><i class="fas fa-leaf width-2rem"></i> Bio</div>
                    <div v-if="item.regional == 1"><i class="fas fa-tractor width-2rem"></i> Regional</div>
                  </div>
                  <button class="btn btn-gruen " :class="{ 'btn-orange': item.booked  }"
                    v-on:click.stop="orderEintrag(item)"
                    v-if="showBuchenBtn(day)">
                    <span v-if="item.booked"><i class="fas fa-toggle-on"></i> Bestellt</span>
                    <span v-if="!item.booked"><i class="fas fa-toggle-off"></i> Bestellen</span>
                  </button>
                  <div v-else >
                    <button v-if="item.booked" class="btn btn-orange"><i class="fas fa-toggle-on"></i> Bestellt</button>
                  </div>
                </div>
              </td>
            <tr v-if="acl.rights.write == 1">
              <td v-bind:key="j" v-for="(day, j) in daysInWeek">
                <button @click="openForm(day)" class="btn width-100p"><i class="fas fa-plus-circle"></i> Hinzufügen</button>
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
    dates: Array,
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
    }

  },
  methods: {

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
        bis: this.lastDayOfWeek.unix()
      });
    },

    openForm: function (day) {
      EventBus.$emit('form--open', {
        item: {date: day}
      });
    },

    getEintrag: function (day) {

      if (this.dates.length <= 0 ) {
        return '';
      }
      var day = this.$date(day).format('YYYY-MM-DD');
      var ret = [];
      this.dates.forEach(function (item) {
        if (day == item.date) {
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
