<template>

  <div>

    <div class="flex-row">
      <h4 class="flex-1"><i class="fas fa fa-pause-circle"></i> Pausenaufsicht</h4>
      <div>
        <a class="si-btn si-btn-border si-btn-icon si-btn-small" href="index.php?page=ext_pausen&view=aufsicht"><i
            class="fas fa-external-link-alt"></i></a>
      </div>
    </div>
    <div v-if="list && list.length >= 1">
      <ul class="noListStyle">
        <li v-bind:key="index" v-for="(item, index) in  list" class="line-oddEven padding-m">

          <h4 class="margin-0">{{ item.title }}</h4>
          <span class="text-grey">{{ item.start }} bis {{ item.end }}</span>

          <div v-if="item.user_id == user_id" class="user margin-t-s text-red"><i class="fas fa fa-user margin-r-s"></i> {{ item.user }}</div>
          <div v-else class="user margin-t-s"><i class="fas fa fa-user margin-r-s"></i> {{ item.user }}</div>

          <div v-if="item.second">
            <div v-if="item.second_id == user_id" class="user margin-t-s text-red"><i class="fas fa fa-user margin-r-s"></i> {{ item.second }}</div>
            <div v-else class="user margin-t-s text-grey"><i class="fas fa fa-user margin-r-s"></i> {{ item.second }}</div>
          </div>
        </li>
      </ul>
    </div>
    <div v-else>
      <div class="padding-m"><i>- Keine Termine -</i></div>
    </div>


  </div>
</template>

<script>

export default {

  name: 'App',
  components: {
  },
  data() {
    return {

      list: []

    };
  },
  created() {
    this.list = window._widget_pausen_aufsicht;
    this.user_id = window._widget_pausen_me;
    //console.log(this.list)
  },
  methods: {}
}
</script>

<style>

</style>
