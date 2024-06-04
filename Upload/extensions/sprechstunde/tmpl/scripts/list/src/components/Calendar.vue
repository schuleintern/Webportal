<template>
  <div>

    <table v-if="acl.read == 1" class="si-table si-table-style-allLeft">
      <thead>
      <tr>
        <td>Datum</td>
        <td>Uhrzeit</td>
        <td>Dauer</td>
        <td>Zugriff</td>
        <td>Benutzer</td>
        <td>Info</td>
        <td></td>
      </tr>
      </thead>
      <tbody class="">
      <tr v-bind:key="j" v-for="(date, j) in dates" :class="{'text-grey': date.status == 9 }">
        <td :class="{'text-orange': date.today == true, 'text-grey': date.done == true}">{{day(date.date)}} - {{date.slot.day}}</td>
        <td class="">{{date.slot.time}}</td>
        <td class="">{{date.slot.duration}} Min</td>
        <td class="">

          <button v-if="date.slot.typ.schueler"
              class="si-btn si-btn-off margin-r-m">Schüler</button>
          <button v-if="date.slot.typ.eltern"
                  class="si-btn si-btn-off margin-r-m">Eltern</button>

        </td>
        <td class="">
          <User v-if="date.user.id != userSelf.id " v-bind:data="date.user"></User>
          <button v-else-if="date.user.id == userSelf.id" class="si-btn si-btn-red"><i class="fa fa-ban"></i> geblockt</button>
        </td>
        <td class="">{{date.info}}</td>
        <td style="text-align: right;">
          <button v-if="acl.delete == 1 && date.status == 0" class="si-btn si-btn-border si-btn-icon" v-on:click="handlerDelete(date)"><i class="fas fa-trash"></i></button>
          <button v-if="date.status == 9" class="si-btn si-btn-red">Abgesagt</button>
        </td>
      </tr>
      </tbody>
    </table>

  </div>
</template>


<script>

import User from '../mixins/User.vue'

export default {
  components: {
    User
  },
  name: 'Calendar',
  props: {
    dates: Array,
    acl: Object
  },
  data() {
    return {
      userSelf: globals.userSelf
    }
  },
  created: function () {

  },
  computed: {

  },
  methods: {

    handlerDelete: function (date) {

      if (confirm("Wirklich löschen?") == true) {
        //console.log(date);

        EventBus.$emit('date--delete', {
          date: date
        });


      }

    },
    day: function (day) {
      return this.$date(day).format('D.MM.YYYY');
    }

  }
}
</script>
