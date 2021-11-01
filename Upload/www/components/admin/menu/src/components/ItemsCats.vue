<template>
  <div class="item-cats">
    <ul class="table_head">
      <li class="flex-row">
        <div class="flex-1 title"></div>
        <div class="flex-1">Page</div>
        <div class="flex-1">Params</div>
        <div class="flex-1 id">ID</div>
      </li>
    </ul>
    <ul>
      <span v-bind:key="index" v-for="(item, index) in items">
        <li class="line-oddEven flex-row" >
          <div class="flex-1 title"><i :class="item.icon"></i> <b>{{item.title}}</b></div>
          <div class="flex-1 text-small">{{item.page}}</div>
          <div class="flex-1 text-small">{{item.params}}</div>
          <div class="flex-1 text-small text-grey id">{{item.id}}</div>
        </li>
        <li v-if="item.items.length >= 1" class="flex-b-100">
          <ItemsItem v-bind:items="item.items"></ItemsItem>
        </li>
      </span>
    </ul>


  </div>
</template>

<script>

import ItemsItem from './ItemsItem.vue';

export default {
  components: {
    ItemsItem
  },
  props: {
    items: Array
  },
  data() {
    return {
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