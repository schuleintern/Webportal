<template>
  <div class="flex-row">

    <div class="flex-1">
      <div>{{item.title}}</div>
      <div class="text-small text-grey">{{item.desc}}</div>
    </div>

    <div class="flex-2">
      <input type="number" v-model="value" v-on:change="handlerToggle()" />
    </div>

  </div>
</template>

<script>

export default {
  name: 'app',
  props: {
    item: Object
  },
  components: {
    
  },
  data: function () {
    return {
      value: 0
    }
  },
  created: function () {
    if (this.item.value) {
      this.value = this.item.value;
    }
  },
  methods: {

    handlerToggle () {

      //console.log('handlerToggle',this.value);
      this.$emit('change', {"value": this.value, "item": this.item} );
    }
    
  }
}
</script>

<style scoped>
</style>
