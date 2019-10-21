<template>
  <div class="nextEvents">
    <h2>Ihre reservierten Objekte (TOD: per ajax nachladen)</h2>
    <ul>
      <li class="header">
        <div>Datum</div>
        <div>Stunde</div>
        <div>Klasse</div>
        <div>Objekt</div>
        <div></div>
      </li>
      <li v-bind:key="index" v-for="(item, index) in dates">
        <div>{{item.ausleiheDatum}}</div>
        <div>{{item.ausleiheStunde}}</div>
        <div>{{item.ausleiheKlasse}}</div>
        <div>{{item.objektName}}</div>
        <div @click="deleteHandler(item)">Löschen</div>
      </li>
    </ul>


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
     
    }
  },
  created: function () {

    EventBus.$on('nextevents--delete-success', data => {

      var that = this;
      this.dates.forEach( function (value, index, array) {
        if( value.ausleiheID == data.ausleiheID) {
          that.dates = that.dates.splice(index,1);
        }
      });

    });

  },
  methods: {

    deleteHandler: function (item) {

      if (!item.ausleiheID) {
        return false;
      }
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
