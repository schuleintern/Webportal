<template>
  <div class="form-style-2">

    <button class="btn btn-grey-line" v-on:click="handlerBack"> Zurück</button>
    <br><br>
    <ul class="noListStyle">
      <li class="line-oddEven padding-t-m padding-b-m">
        <label class="width-7rem padding-l-m">Title</label>
        <input type="text" v-model="item.title" />
      </li>
      <li class="line-oddEven padding-t-m padding-b-m">
        <label class="width-7rem padding-l-m">Icon</label>
        <input type="text" v-model="item.icon" />
      </li>
      <li class="line-oddEven padding-t-m padding-b-m">
        <label class="width-7rem padding-l-m">Params</label>
        <input type="text" v-model="item.params" />
      </li>
      <li>
        <br>
        <button class="btn btn-blau" v-on:click="handlerSubmit">Speichern</button>
      </li>
    </ul>


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

    },
    handlerBack: function () {
      EventBus.$emit('show--set', {
        'show': 'items'
      });
    }

  }

};
</script>

<style>
</style>