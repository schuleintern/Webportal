<template>
  <div>
    <table class="si-table">
      <thead>
        <tr>
          <td class="width-30rem">Name</td>
          <td class="">Alias</td>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(item, index) in list"
          class="line-oddEven">
          <td><button v-on:click="handlerMenuOpen(item)" class="si-btn">{{item.title}}</button></td>
          <td>{{item.alias}}</td>
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