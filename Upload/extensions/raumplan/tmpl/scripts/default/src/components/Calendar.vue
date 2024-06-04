<template>
  <div>

    <div class="si-calendar">
      <div class="si-calendar-header">
        <button class="si-btn  chevron-left" @click="subtractWeek">
          <i class="fa fa-arrow-left"></i>Vor
        </button>
        <button @click="gotoToday"
                class="si-btn si-btn-light">
          <i class="fa fa-home"></i>Heute
        </button>
        <div class="title"><span class="margin-r-l text-green">{{room}}</span> <span class="margin-l-l">{{ $date(firstDayOfWeek).format("DD.") }} - {{ $date(lastDayOfWeek).format("DD. MMMM YYYY") }}</span></div>
        <button class="si-btn chevron-right" @click="addWeek">
          <i class="fa fa-arrow-right"></i>Weiter
        </button>
      </div>


      <table class="si-table">
        <thead>
        <tr>
          <td>Stunde</td>
          <td v-bind:key="j" v-for="(day, j) in daysInWeek"
              :class="{ 'bg-orange': isToday(day) == true}">
            {{ $date(day).format('DD. dd') }}
          </td>
        </tr>
        </thead>
        <tbody class="">
        <tr v-bind:key="j" v-for="(stunde, j) in plan">
          <td class="text-big-2">{{ j + 1 }}</td>
          <td v-bind:key="i" v-for="(day, i) in stunde" v-if="day.day">
            <span v-if="day[0]">
              <div v-bind:key="k" v-for="(unit, k) in day"
                   class="si-box flex-row" v-if="k != 'day'"
                   :class="{ 'si-box-green':  unit.state == 'unique'}"
                  v-on:click="openItem(unit)">
                <div class="margin-b-s flex-b-50">{{ unit.subject }}</div>
                <div class="text-right margin-b-s flex-b-50 padding-r-l">{{ unit.grade }}</div>
                <div class=" text-small flex-b-50">{{ unit.room }}</div>
                <div class="text-small text-right flex-b-50  padding-r-l">{{ unit.teacher }}</div>
              </div>
            </span>
            <span v-else >
              <button class="si-btn si-btn-light"
                      v-on:click.stop="openForm(j+1, day.day[0])">
                <i class="fa fa-plus"></i> Buchen
              </button>
            </span>

          </td>
        </tr>
        </tbody>
      </table>


    </div>

    <ModalForm v-bind:room="room"></ModalForm>
    <ModalItem ></ModalItem>


  </div>
</template>


<script>

import ModalForm from '../mixins/ModalForm.vue'
import ModalItem from '../mixins/ModalItem.vue'

export default {
  components: {
    ModalForm, ModalItem
  },
  name: 'Calendar',
  props: {
    plan: Array,
    acl: Object,
    room: String,
    showDays: Array
  },
  data() {
    return {

      today: this.$date(),
      thisWeek: false,

      prevDays: globals.prevDays

    }
  },
  created: function () {

    this.thisWeek = this.today;
    this.changedDate();

    this.prevDays = parseInt(this.prevDays);

    var that = this;
    EventBus.$on('calender--reload', data => {
      that.changedDate();
      EventBus.$emit('modal-item--close');
      EventBus.$emit('modal-form--close');
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
      for (let i = 0; i < 7; i++) {
        if (this.showDays[foo.format('dd')] == 1) {
          arr.push([foo]);
        }
        foo = foo.add(1, 'day');
      }
      return arr;
    }

  },
  methods: {

    showBuchenBtn: function (day) {
      var prev = this.today.add(this.prevDays, 'day');
      if (prev.isBefore(day)) {
        return true;
      }
      return false;
    },
    isToday: function (day) {
      if (this.today.isSame(day, 'day')) {
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

    openForm: function (stunde, day ) {

      if (stunde && day) {
        EventBus.$emit('modal-form--open', {
          dates: {date: day, stunde: stunde}
        });
      }

    },

    /*
    getEintrag: function (day) {

      if (this.plan.length <= 0) {
        return '';
      }
      var day = this.$date(day).format('YYYY-MM-DD');
      var ret = [];
      this.plan.forEach(function (item) {
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
    },
    */
    openItem: function (unit) {
      if (unit) {
        EventBus.$emit('modal-item--open', {
          unit: unit
        });
      }
    }
  }
}
</script>
