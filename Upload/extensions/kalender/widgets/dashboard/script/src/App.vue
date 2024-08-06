<template>

  <div>

    <div class="flex-row">
      <h4 class="flex-1"><i class="fas fa fa-calendar"></i> Kalender</h4>
      <div>
        <a class="si-btn si-btn-border si-btn-icon si-btn-small" href="index.php?page=ext_kalender"><i class="fas fa-external-link-alt"></i></a>
      </div>
    </div>
    <ul v-if="list && list.length >= 1" class="noListStyle">
      <li v-bind:key="index" v-for="(item, index) in  list" class="line-oddEven padding-m">

        <h4 class="margin-0" :style="'color:'+item.calenderColor" ><i class="far fa-calendar-check margin-r-s"></i>
          <span v-if="item.dateEnd">{{ item.dateStart }} bis {{item.dateEnd}}</span>
          <span v-else-if="item.timeStart != '00:00'" >
              <span v-if="item.timeStart != '00:00'">{{ item.timeStart }}</span>
              <span v-if="item.timeEnd != '00:00'"> - {{ item.timeEnd }}</span>
          </span>
          <span v-else>Heute</span>
        </h4>
        <div class="title">{{ item.title }}</div>
        <div class="info  flex-row text-gey text-small" v-if="item.place || item.comment">
          <div v-if="item.place" class="flex-1"><i class="fas fa-map-marker-alt margin-r-xs"></i>
            {{ item.place }}
          </div>
          <div v-if="item.comment" class="flex-1"><i class="fas fa-comment margin-r-s"></i> <span
              v-html="item.comment"></span></div>
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
    console.log(window)
    this.list = window._widget_kalender_events.today;
    //console.log(this.list)
  },
  methods: {}
}
</script>

<style>

</style>
