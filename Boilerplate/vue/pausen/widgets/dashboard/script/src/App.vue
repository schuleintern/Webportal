<template>

  <div>

    <div class="flex-row">
      <h4 class="flex-1"><i class="fas fa fa-pause-circle"></i> Pausenaufsicht</h4>
      <div>
        <a class="si-btn si-btn-border si-btn-icon si-btn-small" href="index.php?page=ext_pausen&view=aufsicht"><i class="fas fa-external-link-alt"></i></a>
      </div>
    </div>
    <ul v-if="list && list.length >= 1" class="noListStyle">
      <li v-bind:key="index" v-for="(item, index) in  list" class="line-oddEven padding-m">

        {{item.day}}
        <h4 class="margin-0">{{ item.title}}</h4>
        <span class="text-grey">{{ item.start }} bis {{item.end}}</span>
        <div class="user margin-t-s"><i class="fas fa fa-user margin-r-s"></i> {{ item.user }}</div>

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
    this.list = window._widget_pausen_aufsicht;
    //console.log(this.list)
  },
  methods: {}
}
</script>

<style>

</style>
