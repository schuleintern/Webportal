<template>
  <div class="item-cats">
    <button class="si-btn si-btn-light" v-on:click="handlerBack"> Zurück</button>
    <br><br>
    <ul class="si-table si-table-style-firstLeft">
      <li class="flex-row thead">
        <div class="flex-1 title td"></div>
        <div class="flex-1 td"></div>
        <div class="flex-1 td">Page</div>
        <div class="flex-1 td">Params</div>
        <div class="width-7rem td"></div>
        <div class="flex-1 id td">ID</div>
      </li>
    </ul>
    <ul class="si-table si-table-style-firstLeft">
      <div v-bind:key="index" v-for="(item, index) in items" class="tbody margin-b-l">
        <li class="flex-row tr " >
          <div class="flex-1 title flex-center-center td"><i :class="item.icon"></i> <b>{{item.title}}</b></div>
          <div class="flex-1 td"></div>
          <div class="flex-1 text-small flex-center-center td">{{item.page}}</div>
          <div class="flex-1 text-small flex-center-center td">{{item.params}}</div>
          <div class="width-7rem td"><button class="si-btn si-btn-light" v-on:click="handlerFormOpen(item)"><i class="fas fa-plus"></i></button></div>
          <div class="flex-1 text-small text-grey id flex-center-center td">{{item.id}}</div>
        </li>
        <li v-if="item.items.length >= 1" class="flex-b-100">
          <ItemsItem v-bind:items="item.items" v-bind:parent="item"></ItemsItem>
        </li>
      </div>
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