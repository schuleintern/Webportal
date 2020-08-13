<template>
  <div id="app" class="flex">


  

    <div class="box padding-m">
      <h3>Info</h3>
      <div class="padding-m">
        <ul class="flex noListStyle">
          <li><label class="margin-r-s">Name:</label>{{db_details.DB_Name}}</li>
          <li><label class="margin-r-s">Tabellenanzahl:</label>{{db_details.Total_Tables}}</li>
          <li><label class="margin-r-s">Einträge:</label>{{db_details.Total_Tables_Row}}</li>
          <li><label class="margin-r-s">Größe:</label>{{db_details[3]}} mb</li>
          <li><label class="margin-r-s">Freier Speicher:</label>{{db_details[4]}} mb</li>
        </ul>
      </div>
    </div>



    <div class="box padding-m">
      <h3>Tabellen</h3>
      <div class="padding-m">
        <ul class="flex noListStyle">
          <li>
            <ul class="flex-row noListStyle text-bold">
              <li class="flex-1">Name</b></li>
              <li class="flex-1">Zeilen</li>
              <li class="flex-1">Auto_increment</li>
              <li class="flex-1">Größe</li>
            </ul>
          </li>
          <li v-bind:key="index" v-for="(entry, index) in  tables_details"
            class="line-oddEven">
            <ul class="flex-row noListStyle text-lineHeight-m">
              <li class="flex-1">{{entry.Name}}</li>
              <li class="flex-1">{{entry.Rows}}</li>
              <li class="flex-1">{{entry.Auto_increment}}</li>
              <li class="flex-1">{{entry.Data_length}}</li>
            </ul>
          </li>
        </ul>
      </div>
    </div>


  </div>
</template>

<script>

//const axios = require('axios').default;

export default {
  name: 'app',
  components: {

  },
  data: function () {
    return {
      selfURL: globals.selfURL,
      tables_details: globals.tables_details,
      db_details: globals.db_details
    }
  },
  created: function () {

    //console.log(this.tables_details);
  },
  methods: {
  
  }
}
</script>

<style>


</style>
