<template>
  <div class="flex-row">

    <div class="flex-1">
      <div>{{item.title}}</div>
      <div class="text-small text-grey">{{item.desc}}</div>
    </div>
    <div class="flex-2">
      <button 
        v-if="value == 1"
        v-on:click="handlerToggle()"
        class="btn text-green"><i class="fa fas fa-toggle-on"></i> An</button>
      <button 
        v-else-if="value == 0 || value == undefined"
        v-on:click="handlerToggle()"
        class="btn"><i class="fa fas fa-toggle-off"></i> Aus</button>
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
    if (this.item.value == 1) {
      this.value = 1;
    } else {
      this.value = 0;
    }
       
  },
  methods: {

    handlerToggle () {

      //console.log('handlerToggle',this.value);
      if (this.value) {
        this.value = 0;
      } else {
        this.value = 1;
      }
      this.$emit('change', {"value": this.value, "item": this.item} );
    }

  }
}
</script>

<style scoped>
</style>
