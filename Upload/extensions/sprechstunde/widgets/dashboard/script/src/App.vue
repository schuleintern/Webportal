<template>

  <div>

    <ul v-if="list && list.length >= 1" class="noListStyle">
      <li v-bind:key="index" v-for="(item, index) in  list" class=" line-oddEven padding-m ">

        <h4 class="flex-1">
          <i class="far fa-calendar-check margin-r-s"></i> <span v-if="item.slot" class="">{{ item.slot.day }} - </span>
          {{ item.date }}
          <span class="margin-r-l " v-if="item.slot"> - {{ item.slot.time }} Uhr</span>
        </h4>


        <div v-if="item.slot" class="margin-b-s">
          <div class="text-bold">{{ item.slot.title }}</div>
        </div>


        <button v-if="item.block == 1" class="si-btn si-btn-red"><i class="fa fa-ban"></i> Geblockt</button>
        <User v-else size="icon" v-bind:data="item.user"></User>
        <div class="margin-t-s">
            <span v-if="item.medium" class="flex-1 padding-s text-small margin-r-m">
              <span v-if="item.medium == 'live'"><i class="fa fa-handshake"></i> Live</span>
              <span v-if="item.medium == 'phone'"><i class="fa fa-phone"></i> Telefon</span>
              <span v-if="item.medium == 'viko'"><i class="fa fa-video"></i> Videokonferenz</span>
            </span>
          <span class="">{{ item.slot.duration }} min</span>
        </div>
        <div v-if="item.info" class="padding-t-m padding-l-s" style="white-space: normal;"><i
            class="fas fa-info-circle"></i> {{ item.info }}
        </div>


      </li>
    </ul>
    <div v-else>
      <div class="padding-m"><i>- Keine Termine -</i></div>
    </div>


  </div>
</template>

<script>

import User from "./mixins/User.vue";

export default {

  name: 'App',
  components: {User},
  data() {
    return {

      list: []

    };
  },
  created() {
    this.list = window._widget_sprechstunde_dates;
  },
  methods: {}
}
</script>

<style>

</style>
