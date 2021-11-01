<template>
  <div>

    <ul>
      <li class="line-oddEven">
        <label>Title</label>
        <input type="text" v-model="item.title" />
      </li>
      <li class="line-oddEven">
        <label>Icon</label>
        <input type="text" v-model="item.icon" />
      </li>
    </ul>
    <button class="btn btn-blue" v-on:click="handlerSubmit">Speichern</button>

  </div>
</template>

<script>



export default {
  components: {

  },
  props: {
    item: Array
  },
  data() {
    return {
    };
  },
  created: function () {
  },
  methods: {

    handlerSubmit: function () {

      if (!this.item.id) {
        return false;
      }
      EventBus.$emit('item-form--submit', {
        item: this.item
      });

    }

  }

};
</script>

<style>
</style>