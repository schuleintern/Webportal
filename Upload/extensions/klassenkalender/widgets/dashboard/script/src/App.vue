<template>

  <div>

    <div class="flex-row">
      <h4 class="flex-1"><i class="fas fa fa-graduation-cap"></i> Klassenkalender</h4>
      <div>
        <a class="si-btn si-btn-border si-btn-icon si-btn-small" href="index.php?page=ext_klassenkalender"><i class="fas fa-external-link-alt"></i></a>
      </div>
    </div>
    <ul v-if="list && list.length >= 1" class="noListStyle">
      <li v-bind:key="index" v-for="(item, index) in  list" class="line-oddEven padding-m">

        <h4 class="margin-0" :style="'color:'+item.calenderColor">
          <i v-if="item.typ == 'event'" :style="getStyle(item)" class="far fa-calendar-check margin-r-s"></i>
          <i v-if="item.typ == 'lnw'" :style="getStyle(item)" class="fas fa-calendar-day margin-r-s"></i>
          <span v-if="item.kalender && item.kalender.title" :style="getStyle(item)">{{ item.kalender.title }}: </span>
          <span v-if="item.dateEnd">{{ item.dateStart }} bis {{item.dateEnd}}</span>
          <span v-else-if="item.timeStart != '00:00'" >
              <span v-if="item.stunde" class="padding-m-m"> {{item.stunde}}. Stunde </span>
              <span v-else>
                <span v-if="item.timeStart != '00:00'" >{{ item.timeStart }}</span>
                <span v-if="item.timeEnd != '00:00'" > - {{ item.timeEnd }}</span>
              </span>

          </span>
          <span v-else>Heute</span>
        </h4>
        <div class="title text-big-1 text-bold" >
          <span class="spacer" :style="getLnwStyle(item)"></span>{{ item.title }}</div>
        <div v-if="item.stunde">
          <span v-if="item.timeStart != '00:00'" >{{ item.timeStart }}</span>
          <span v-if="item.timeEnd != '00:00'" > - {{ item.timeEnd }}</span>
        </div>
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
    //console.log(window)
    this.list = window._widget_klassenkalender_events.today;
    //console.log(this.list)
  },
  methods: {
    getStyle(item) {

      if (item && item.kalender && item.kalender.color) {
        return 'color:'+item.kalender.color;
      }
    },
    getLnwStyle(item) {

      if (item && item.lnw && item.lnw.color) {
        return 'margin-right:0.6rem; border-radius: 0.3rem; top: 0.2rem; position: relative; width: 1.6rem; height: 1.6rem; display: inline-block; background-color:'+item.lnw.color;
      }
    }
  }
}
</script>

<style>

</style>
