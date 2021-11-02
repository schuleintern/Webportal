<template>
  <div class="item-cats">
    <button class="btn btn-grey-line" v-on:click="handlerBack"> Zurück</button>
    <br><br>
    <ul class="table_head">
      <li class="flex-row">
        <div class="flex-1 title"></div>
        <div class="flex-1"></div>
        <div class="flex-1">Page</div>
        <div class="flex-1">Params</div>
        <div class="width-7rem"></div>
        <div class="flex-1 id">ID</div>
      </li>
    </ul>
    <ul>
      <span v-bind:key="index" v-for="(item, index) in items">
        <li class="flex-row" >
          <div class="flex-1 title flex-center-center"><i :class="item.icon"></i> <b>{{item.title}}</b></div>
          <div class="flex-1"></div>
          <div class="flex-1 text-small flex-center-center">{{item.page}}</div>
          <div class="flex-1 text-small flex-center-center">{{item.params}}</div>
          <div class="width-7rem"><button class="btn btn-grey-line" v-on:click="handlerFormOpen(item)"><i class="fas fa-plus"></i></button></div>
          <div class="flex-1 text-small text-grey id flex-center-center">{{item.id}}</div>
        </li>
        <li v-if="item.items.length >= 1" class="flex-b-100">
          <ItemsItem v-bind:items="item.items" v-bind:parent="item"></ItemsItem>
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

    handlerBack: function () {
      EventBus.$emit('show--set', {
        'show': 'menu'
      });
    },
    handlerFormOpen: function (item) {
      EventBus.$emit('item-form--open', {
        parent: item
      });
    }

  }

};
</script>

<style>
</style>