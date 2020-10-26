<template>
  <div class="nextEvents">
    <h2>Ihre reservierten Objekte</h2>
    <table class="">
      <thead>
        <tr class="header">
          <td class="table-width-date">Datum</td>
          <td class="table-width-hour">Stunde</td>
          <td class="table-width-object">Objekt</td>
          <td></td>
          <td class="table-width-object">Klasse</td>
          <td class="table-width-del"></td>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(item, index) in dates">
          <td>{{item.ausleiheDatum}}</td>
          <td>{{item.ausleiheStunde}}</td>
          <td>{{item.objektName}} <span v-if="item.sub.length > 0"> ({{item.part}}/{{item.sum}})</span></td>
          <td><span v-if="item.sub.length > 0">{{item.sub}}</span></td>
          <td>{{item.ausleiheKlasse}}</td>
          <td>
            <div v-if="comit -1 != index || false"
              @click="deleteHandler(item, index)" class="text-red" >
              <i class="fa fa-trash"></i>
            </div>
            <button v-if="comit -1 == index" class="btn btn-danger"
              @click="deleteSecondHandler(item, index)">
              Entgültig entfernen!</button>
          </td>
        </tr>
      </tbody>
    </table>


  </div>
</template>

<script>
export default {
  name: 'NextEvents',
  props: {
    dates: Array
  },
  data: function () {
    return {
     comit: false
    }
  },
  created: function () {

    var that = this;

    EventBus.$emit('nextevents--reload', {});
    
  },
  methods: {

    deleteHandler: function (item, index) {

      if (!item.ausleiheID) {
        return false;
      }
      this.comit = index+1;

    },
    deleteSecondHandler: function (item, index) {

      if (!item.ausleiheID) {
        return false;
      }
      this.comit = false;
      EventBus.$emit('nextevents--delete', {
        ausleiheID: item.ausleiheID
      });

    }

  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
