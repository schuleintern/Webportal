<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">

        <button v-if="delButton == false" class="si-btn" v-on:click="handlerDelDayGroup(item)">
          <i class="fa fa-trash"></i> Aufsicht entfernen</button>
        <button v-if="delButton == true" class="si-btn si-btn-red" v-on:click="handlerDelDayGroupSecond(item)">
          <i class="fa fa-trash"></i> Aufsicht entfernen</button>


        <table class="si-table si-table-style-allLeft">
          <thead>
          <tr>
            <th width="1rem"></th>
            <th width="" v-on:click="handlerSort('vorname')" class="curser-sort">Vorname</th>
            <th width="" v-on:click="handlerSort('nachname')" class="curser-sort">Nachname</th>
            <th width="" v-on:click="handlerSort('gender')" class="curser-sort"></th>
            <th width="" v-on:click="handlerSort('klasse')" class="curser-sort">Klasse</th>
            <th>Tage</th>
          </tr>
          </thead>
          <tbody>
          <tr v-bind:key="index" v-for="(item, index) in  vlist">
            <td class="text-grey text-small">{{index+1}}</td>
            <td class="">
              {{ item.vorname }}
            </td>
            <td class="padding-l-m">
              {{ item.nachname }}
            </td>
            <td class="">
              {{ item.gender }}
            </td>
            <td class="">
              {{ item.klasse }}
            </td>
            <td>
              <span class="si-btn si-btn-off" v-bind:key="i" v-for="(day, i) in  item.days" v-if="day">{{i}}</span>
            </td>

          </tr>
          </tbody>
        </table>

      </div>
    </div>

  </div>
</template>

<script>



export default {

  components: {

  },
  data() {
    return {
      open: false,
      schueler: [],
      item: false,
      sort: {
        column: 'vorname',
        order: true
      },
      delButton: false
    };
  },
  props: {
    //schueler: Array
  },
  computed: {
    vlist: function () {
      if (this.schueler) {
        let data = this.schueler;
        if (data.length > 0) {

          // SORTIERUNG
          if (this.sort.column ) {
            if (this.sort.order) {
              return data.sort((a, b) => a[this.sort.column].localeCompare(b[this.sort.column]))
            } else {
              return data.sort((a, b) => b[this.sort.column].localeCompare(a[this.sort.column]))
            }
          }

          return data;

        }

      }
      return [];
    }


  },
  created: function () {
    var that = this;
    EventBus.$on('modal-schueler--open', data => {

      //that.schueler = [];
      if (data.schueler) {
        that.schueler = data.schueler;
      }
      if (data.item) {
        that.item = data.item;
      }

      that.open = true;
    });
    EventBus.$on('modal-schueler--close', data => {
      that.open = false;
      //that.item = {};
    });
  },
  methods: {
    handlerDelDayGroup: function (data) {

      this.delButton = true;
    },
    handlerDelDayGroupSecond: function (data) {

      EventBus.$emit('date--delete', {
        item: data
      });
      this.delButton = false;
      EventBus.$emit('modal-schueler--close');
    },
    handlerClose: function () {
      EventBus.$emit('modal-schueler--close');
    },

    handlerSort: function (column) {
      if (column) {
        this.sort.column = column;
        //console.log('hand', this.sort.order);
        if (this.sort.order == true) {
          this.sort.order = false;
        } else {
          this.sort.order = true;
        }
        //this.forceRerender();
      }
    },
  }


};
</script>

<style>

</style>