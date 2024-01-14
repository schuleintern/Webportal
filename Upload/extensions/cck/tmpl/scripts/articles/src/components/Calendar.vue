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
          <td width="10%">Stunde</td>
          <td v-bind:key="j" v-for="(day, j) in daysInWeek" :width="90/daysInWeek.length+'%'"
              :class="{ 'bg-orange': isToday(day) == true}">
            {{ $date(day).format('DD. dd') }}
          </td>
        </tr>
        </thead>
        <tbody class="">
        <tr v-bind:key="j" v-for="(stunde, j) in plan">
          <td class="">
          {{stunde.label}}
          </td>
          <td v-bind:key="i" v-for="(day, i) in stunde" v-if="day.day">


            <span v-if="day.slots">
              <span v-bind:key="s" v-for="(slot, s) in day.slots" >

                <div v-if="slot.dateSet != true" class="si-box">

                  <div class="flex-row">
                    <div class="flex-1 text-big-m">{{slot.time}}</div>
                    <div class="flex-1 flex flex-end text-right">{{slot.duration}} min</div>
                  </div>
                  <div class="padding-t-m padding-b-m">
                    {{slot.title}}
                  </div>
                  <User v-if="slot.user_id != userSelf.id" v-bind:data="slot.user"></User>

                  <div v-if="slot.date" class="si-box si-box-green">
                    <h4><i class="far fa-calendar-check margin-r-s"></i> Termin</h4>
                    <User v-bind:data="slot.date.user"></User>
                    <div v-if="slot.date.info" class="padding-t-m">
                      <i class="fas fa-info-circle"></i> {{slot.date.info}}
                    </div>
                  </div>

                  <div v-else class="text-right padding-t-s">
                    <button v-if="isFuture(day.day) && slot.user_id != userSelf.id" class="si-btn"
                              v-on:click.stop="openForm(day.day, slot)">
                      <i class="fa fa-plus"></i> Buchen
                    </button>
                  </div>
                </div>

              </span>
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
import User from "../mixins/User.vue";

export default {
  components: {
    User, ModalForm, ModalItem
  },
  name: 'Calendar',
  props: {
    plan: Array,
    acl: Object,
    room: String,
    showDays: Array,
    userSelf: Array
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
        if (this.showDays && this.showDays[foo.format('dd')] == 1) {
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
    isFuture: function (day) {
      if (this.today.isBefore(day, 'day')) {
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

    openForm: function (day, slot) {
      if (day && slot) {
        EventBus.$emit('modal-form--open', {
          slot: slot,
          day: day
        });
      }
    },
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
