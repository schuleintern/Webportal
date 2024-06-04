<template>

  <div class="">

    <div class="flex-row">
      <div class="flex-1">
        <a href="#list" class="si-btn si-btn-light" v-on:click="handlerBack"><i class="fa fa-angle-left"></i> Zurück</a>
      </div>

    </div>

    <h2 class="padding-b-l">{{ item.vorname }} {{ item.nachname }}</h2>

    <User v-if="item" v-bind:data="item"></User>

    <div class="si-form">
      <ul>
        <li>
          <label>Anzahl der Tage</label>
          <input type="text" v-model="item.anz" v-on:change="handlerChange(item)">
        </li>
        <li v-bind:key="index" v-for="(day, index) in  showDays" v-if="day" >
          <span v-if="day">
            <label v-if="index == 'mo'">Montag</label>
            <label v-else-if="index == 'di'">Dienstag</label>
            <label v-else-if="index == 'mi'">Mittwoch</label>
            <label v-else-if="index == 'do'">Donnerstag</label>
            <label v-else-if="index == 'fr'">Freitag</label>
            <label v-else-if="index == 'sa'">Samstag</label>
            <label v-else-if="index == 'sa'">Sonntag</label>
            <div>
              <button v-if="item.days[index]" class="si-btn si-btn-toggle-on" v-on:click="handlerToggleDay(index,item)">
                <i class="fa fas fa-toggle-on"></i> An</button>
              <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggleDay(index,item)">
                <i class="fa fas fa-toggle-off"></i> Aus</button>

              <div v-if="item.days[index]" class="blockInline">
                <select v-model="item.days[index].group" v-on:change="handlerChangeDay(item)">
                  <option v-bind:key="index" v-for="(item, index) in  groups" :value="item.id">{{item.title}}</option>
                </select>

                <input v-model="item.days[index].info" v-on:change="handlerChangeDay(item)" type="text" placeholder="Info" class="">
              </div>
            </div>
          </span>
        </li>

        <li>
          <label>Info</label>
          <input type="text" v-model="item.info" v-on:change="handlerChange(item)">
        </li>


      </ul>
    </div>


    <div class="si-box" v-if="absenz && absenz.length >= 1">
      <h3>Absenzen</h3>

      <table class="si-table" >
        <thead>
          <tr>
            <th>Von</th>
            <th>Bis</th>
            <th>Stunden</th>
            <th width="30%">Hinweis</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-bind:key="index" v-for="(item, index) in  absenz" class="">
            <td>{{ item.datum_start }}</td>
            <td>{{ item.datum_end }}</td>
            <td>{{ item.stunden }}</td>
            <td><span class="text-small" v-html="item.bemerkung"></span></td>
            <td><button v-if="item.stunden == 'ganztag'" @click="handlerDelAbsenz(item)"  class="si-btn si-btn-red si-btn-icon"><i class="fa fa-trash"></i></button></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="si-box" v-if="beurlaubung && beurlaubung.length >= 1">
      <h3>Beurlaubungsanträge</h3>

      <table class="si-table" >
        <thead>
          <tr>
            <th>Status</th>
            <th>Erstellt</th>
            <th>Datum</th>
            <th>Stunden</th>
            <th width="30%">Begründung</th>
            <th>Hinweis</th>
          </tr>
        </thead>
        <tbody>
          <tr v-bind:key="index" v-for="(item, index) in  beurlaubung" class="">
            <td>
              <button v-if="item.status == 1" class="si-btn si-btn-icon si-btn-curser-off si-btn-active"><i
                  class="fa fa-question"></i></button>
              <button v-if="item.status == 2 || item.status == 21"
                class="si-btn si-btn-icon si-btn-curser-off si-btn-green"><i class="fa fa-check"></i></button>
              <button v-if="item.status == 3" class="si-btn si-btn-icon si-btn-curser-off si-btn-red"><i
                  class="fa fa-ban"></i></button>
            </td>
            <td>{{ item.createdTime }}</td>
            <td>{{ item.datumStart }}</td>
            <td>{{ item.stunden }}</td>
            <td><span class="text-small" v-html="item.info"></span></td>
            <td>
              <div class="" v-if="item.doneInfo">{{ item.doneInfo }}</div>
              <div class="text-small" v-if="item.doneInfo">{{ item.doneDate }}</div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>




  </div>

</template>


<script>

import User from './../mixins/User.vue'

export default {
  components: {
    User
  },
  name: 'List',
  props: {
    item: Object,
    acl: Object,
    groups: Array,
    showDays: Object,
    beurlaubung: Array,
    absenz: Array
  },
  data() {
    return {
      deleteItem: false,
      sort: {
        column: false,
        order: true
      },
      tabActive: false,
      deleteTab: false
    }
  },
  computed: {

  },
  watch: {

  },
  created: function () {
  },
  mounted() {

  },
  methods: {

    handlerDelAbsenz: function (item) {

      EventBus.$emit('absenz--del', {
        item: item
      });

    },

    handlerBack: function () {
      this.deleteItem = false;
      this.deleteTab = false;
      this.tabActive = false;
      //this.item = [];
      EventBus.$emit('tab--open', {
        tabOpen: 'list'
      });
    },

    handlerToggleDay: function (day, item) {


      if (item.days[day]) {
        item.days[day] = false;
      } else {
        item.days[day] = {group: 0, info: '' };
      }

      EventBus.$emit('item--change', {
        item: item,
        callback: function (data) {
          item.days = data.days;
        }
      });

    },
    handlerChangeDay: function (item) {


      EventBus.$emit('item--change', {
        item: item,
        callback: function (data) {
          item.days = data.days;
        }
      });

    },
    handlerChange: function (item) {


      EventBus.$emit('item--change', {
        item: item,
        callback: function (data) {
          //item.days = data.days;
        }
      });

    }
  }
}
</script>
