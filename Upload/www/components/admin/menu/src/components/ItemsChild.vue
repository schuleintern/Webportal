<template>
  <ul>
    <span v-bind:key="index" v-for="(item, index) in items">
      <li class=" flex-row" >
          <div class="flex-1 title"><a href="#" v-on:click="handlerOpenItem(item)"><i :class="item.icon"></i> {{item.title}}</a></div>
          <div class="flex-1 text-small">{{item.page}}</div>
          <div class="flex-1 text-small">{{item.params}}</div>
          <div class="flex-1 text-small text-grey id">{{item.id}}</div>
      </li>
    </span>
  </ul>


</template>

<script>

export default {
  components: {
    //Items
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

    handlerOpenItem: function (item) {
      EventBus.$emit('item-form--open', {
        item: item
      });
    }

  }

};
</script>

<style>
</style>