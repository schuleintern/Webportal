<template>
  <div class="">

    <a class="si-btn si-btn-border" href="index.php?page=ext_raumplan">
      <i class="fa fas fa-door-open"></i> Mein Raumplan -  Heute <span v-if="count > 0" class=" margin-l-l label bg-red">{{count}}</span>
    </a>

    <table class="si-table si-table-small" >
      <thead>
      <tr>
        <td>Stunde</td>
        <td>Klasse</td>
        <td>Fach</td>
        <td>Raum</td>
      </tr>
      </thead>
      <tbody>
      <tr v-if="list && list.length >= 1" v-bind:key="index" v-for="(item, index) in  list"
          class="">
        <td>{{item.stunde}}</td>
        <td>{{item.klasse}}</td>
        <td>{{item.fach}}</td>
        <td>{{item.raum}}</td>
      </tr>
      <tr v-if="list.length == 0">
        <td colspan="4"> - keine Inhalte vorhanden -</td>
      </tr>

      </tbody>
    </table>


  </div>
</template>

<script>

//onst axios = require('axios').default;

export default {
  components: {
  },
  data() {
    return {

      count: globals_widget_raumplan_mytoday.count,
      list: globals_widget_raumplan_mytoday.list

    };
  },
  created: function () {

  },
  methods: {

  }

};
</script>

<style>

</style>