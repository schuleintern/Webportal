<template>

  <div>


    <ul v-if="list && list.length >= 1">
      <li v-bind:key="index" v-for="(item, index) in  list" class="">


        <div  class="labelTime">
          <span v-if="item.timeStart != '00:00'">{{ item.timeStart }}</span>
          <span v-if="item.timeEnd != '00:00'"> - {{ item.timeEnd }}</span>
        </div>


        <div class="labelDay">{{ item.title }}</div>

        <div v-if="item.place">
          <label class="text-small text-gey margin-r-s"><i class="fas fa-map-marker-alt margin-r-s"></i>Ort:</label>
          <span v-html="item.place"></span>
        </div>
        <div v-if="item.comment" class="">
          <label class="text-small text-gey margin-r-s"><i class="fas fa-comment margin-r-s"></i>Notiz:</label>
          <br>
          <span v-html="item.comment"></span>
        </div>


      </li>
    </ul>
    <div v-else>
      <div class="padding-m"><i>- Keine Termine -</i></div>
    </div>


  </div>
</template>

<script>


export default {

  name: 'App',
  components: {},
  data() {
    return {

      list: []

    };
  },
  created() {
    this.list = window._widget_kalender_events.today;
  },
  methods: {}
}
</script>

<style>

</style>
