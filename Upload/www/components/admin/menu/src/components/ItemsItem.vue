<template>

    <ul class="">
      <draggable v-model="items" handle=".sortHandle" group="sort" @start="drag=true" @end="drag=false" @change="handlerItemSort" >
      <div v-bind:key="index" v-for="(item, index) in items" class="tr">
        <li class=" flex-row" >
          <div class="flex-1 title flex-center-center td"><a href="#" v-on:click="handlerOpenItem(item)"><i :class="item.icon"></i> {{item.title}}</a></div>
          <div class="flex-1 text-small td">
            <button
                v-if="item.active == 1"
                v-on:click="handlerToggleActive(item)"
                class="si-btn si-btn-light text-green"><i class="fas fa-toggle-on"></i></button>
            <button
                v-if="item.active == 0"
                v-on:click="handlerToggleActive(item)"
                class="si-btn si-btn-light"><i class="fas fa-toggle-off"></i></button>
            <button class="sortHandle si-btn si-btn-light"><i class="fas fa-sort"></i></button>
          </div>
          <div class="flex-1 text-small flex-center-center td">{{item.page}}</div>
          <div class="flex-1 text-small flex-center-center td">{{item.params}}</div>
          <div class="width-7rem td"><button class="si-btn si-btn-light" v-on:click="handlerFormOpen(item)"><i class="fas fa-plus"></i></button></div>
          <div class="flex-1 text-small text-grey id flex-center-center td">{{item.id}}</div>
        </li>

        <li v-if="item.items.length >= 1" class="flex-b-100">
          <ItemsChild v-bind:items="item.items" v-bind:parent="item"></ItemsChild>
        </li>
      </div>
      </draggable>
    </ul>

</template>

<script>

import draggable from 'vuedraggable'

import ItemsChild from './ItemsChild.vue';

export default {
  components: {
    draggable,
    ItemsChild
  },
  props: {
    items: Array,
    parent: Array
  },
  data() {
    return {
    };
  },
  created: function () {
  },
  methods: {

    handlerItemSort: function () {
      var a = 1;
      var post = [];
      this.items.forEach(function (o,i) {
        o.sort = a;
        a++;
        post.push({
            "id": o.id,
            "sort": o.sort
        });
      });
      EventBus.$emit('item-form--sort', {
        items: post
      });
    },
    handlerOpenItem: function (item) {
      EventBus.$emit('item-form--open', {
        item: item,
        parent: this.parent
      });
    },
    handlerToggleActive: function (item) {
      if (!item.id) {
        return false;
      }
      EventBus.$emit('item-form--active', {
        item: item
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