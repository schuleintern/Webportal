<template>
  <ul>
    <span v-bind:key="index" v-for="(item, index) in items">
      <li class=" flex-row" >
          <div class="flex-1 title flex-center-center"><a href="#" v-on:click="handlerOpenItem(item)"><i :class="item.icon"></i> {{item.title}}</a></div>
          <div class="flex-1 text-small">
            <button
                v-if="item.active == 1"
                v-on:click="handlerToggleActive(item)"
                class="btn text-green"><i class="fas fa-toggle-on"></i></button>
            <button
                v-else
                v-on:click="handlerToggleActive(item)"
                class="btn"><i class="fas fa-toggle-off"></i></button>
          </div>
          <div class="flex-1 text-small flex-center-center">{{item.page}}</div>
          <div class="flex-1 text-small flex-center-center">{{item.params}}</div>
          <div class="flex-1 text-small text-grey id flex-center-center">{{item.id}}</div>
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
    },
    handlerToggleActive: function (item) {
      if (!item.id) {
        return false;
      }
      EventBus.$emit('item-form--active', {
        item: item
      });
    }

  }

};
</script>

<style>
</style>