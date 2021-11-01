<template>
  <div>
    <table class="table_1">
      <thead>
        <tr>
          <td>Name</td>
          <td>Alias</td>
          <td>ID</td>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(item, index) in list"
          class="line-oddEven">
          <td><a href="#" v-on:click="handlerMenuOpen(item)">{{item.title}}</a></td>
          <td>{{item.alias}}</td>
          <td>{{item.id}}</td>
        </tr>
      </tbody>
    </table>

  </div>
</template>

<script>

export default {
  props: {
    list: Array
  },
  data() {
    return {
    };
  },
  created: function () {
  },
  methods: {

    handlerMenuOpen: function (item) {

      //console.log(item);

      EventBus.$emit('menu--open', {
        item: item
      });

    }

  }

};
</script>

<style>
</style>