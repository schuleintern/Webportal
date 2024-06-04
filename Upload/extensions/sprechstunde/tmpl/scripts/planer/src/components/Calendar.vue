<template>
  <div>

    <span v-if="acl.write == 1" >
      <button class="si-btn"
              v-on:click="openForm()">
        <i class="fa fa-plus"></i> Neuen Slot Hinzufügen
      </button>
    </span>

    <table v-if="acl.read == 1" class="si-table">
      <thead>
      <tr>
        <td width="10%">Stunde</td>
        <td v-bind:key="j" v-for="(day, j) in daysInWeek" :width="90/daysInWeek.length+'%'">
          {{ $date(day).format('dd') }}
        </td>
      </tr>
      </thead>
      <tbody class="">
      <tr v-bind:key="j" v-for="(stunde, j) in week">
        <td class="">{{stunde.label}}</td>
        <td v-bind:key="i" v-for="(day, i) in stunde" v-if="day.day">

          <div v-bind:key="s" v-for="(slot, s) in hasSlots(day.day, stunde.label)" class="si-box"
               v-on:click="openItem(slot)">

            <div class="flex-row">
              <div class="flex-1 text-big-m">{{slot.time}}</div>
              <div class="flex-1 flex flex-end text-right">{{slot.duration}} min</div>
            </div>
            <div class="padding-t-m">
              {{slot.title}}
            </div>
            <div class="padding-t-m" v-if="slot.typ">
              <button
                  v-if="slot.typ.schueler"
                  class="si-btn si-btn-off"><i class="fa fas fa-user"></i> Schüler</button>
              <button
                  v-if="slot.typ.eltern"
                  class="si-btn si-btn-off"><i class="fa fas fa-users"></i> Eltern</button>
            </div>

          </div>

        </td>
      </tr>
      </tbody>
    </table>


    <ModalForm v-bind:showDays="showDays"  v-bind:formData="formData" ></ModalForm>
    <ModalItem v-bind:acl="acl"></ModalItem>

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
    week: Array,
    slots: Array,
    acl: Object,
    showDays: Array,
    formData: Array
  },
  data() {
    return {
      today: this.$date(),
      thisWeek: false,
    }
  },
  created: function () {

    this.thisWeek = this.today;
    this.changedDate();

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

    hasSlots: function (tag, stunde) {
      let day = this.$date(tag).format('dd');
      let hours = stunde.split(':');
      let hour = parseInt(hours[0]+hours[1]);
      let hourNext = hour +100;
      let ret = [];
      if (day && hour) {
        this.slots.forEach((o) => {

          let hs = o.time.split(':');
          let h = parseInt(hs[0]+hs[1]);

          if (h && o.day == day && h >= hour && h < hourNext ) {
            ret.push(o);
          }
        })
      }
      if (ret.length > 0) {
        return ret;
      }
      return false;
    },

    changedDate: function () {
      EventBus.$emit('calendar--changedDate', {
        von: this.firstDayOfWeek.unix(),
        bis: this.lastDayOfWeek.unix()
      });
    },

    openItem: function (slot) {
      if (slot) {
        EventBus.$emit('modal-item--open', {
          slot: slot
        });
      }
    },
    openForm: function () {
      EventBus.$emit('modal-form--open', {
      });
    }

  }
}
</script>
