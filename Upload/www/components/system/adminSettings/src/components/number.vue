<template>
  <div>

    {{item.title}}
    <div class="text-small text-grey">{{item.desc}}</div>

    <input type="number" v-model="value" v-on:change="handlerToggle()" />
    

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
