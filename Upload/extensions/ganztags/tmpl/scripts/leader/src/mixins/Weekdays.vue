<template>
  <div>

    <span>
      <button v-if="days.mo == true" class="si-btn si-btn-toggle-on" v-on:click="handlerToggle($event,'mo')"><i
          class="fa fas fa-toggle-on"></i> Mo</button>
      <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggle($event,'mo')"><i
          class="fa fas fa-toggle-off"></i> Mo</button>
    </span>
    <span>
      <button v-if="days.di == true" class="si-btn si-btn-toggle-on" v-on:click="handlerToggle($event, 'di')"><i
          class="fa fas fa-toggle-on"></i> Di</button>
      <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggle($event,'di')"><i
          class="fa fas fa-toggle-off"></i> Di</button>
    </span>
    <span>
      <button v-if="days.mi == true" class="si-btn si-btn-toggle-on" v-on:click="handlerToggle($event, 'mi')"><i
          class="fa fas fa-toggle-on"></i> Mi</button>
      <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggle($event,'mi')"><i
          class="fa fas fa-toggle-off"></i> Mi</button>
    </span>
    <span>
      <button v-if="days.do == true" class="si-btn si-btn-toggle-on" v-on:click="handlerToggle($event, 'do')"><i
          class="fa fas fa-toggle-on"></i> Do</button>
      <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggle($event,'do')"><i
          class="fa fas fa-toggle-off"></i> Do</button>
    </span>
    <span>
      <button v-if="days.fr == true" class="si-btn si-btn-toggle-on" v-on:click="handlerToggle($event, 'fr')"><i
          class="fa fas fa-toggle-on"></i> Fr</button>
      <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggle($event,'fr')"><i
          class="fa fas fa-toggle-off"></i> Fr</button>
    </span>
    <span>
      <button v-if="days.sa == true" class="si-btn si-btn-toggle-on" v-on:click="handlerToggle($event, 'sa')"><i
          class="fa fas fa-toggle-on"></i> Sa</button>
      <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggle($event,'sa')"><i
          class="fa fas fa-toggle-off"></i> Sa</button>
    </span>
    <span>
      <button v-if="days.so == true" class="si-btn si-btn-toggle-on" v-on:click="handlerToggle($event, 'so')"><i
          class="fa fas fa-toggle-on"></i> So</button>
      <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggle($event,'so')"><i
          class="fa fas fa-toggle-off"></i> So</button>
    </span>


  </div>
</template>

<script>

export default {
  data() {
    return {

    };
  },
  props: {
    days: Object
  },
  created: function () {

  },
  watch: {

  },
  methods: {

    handlerToggle: function (e, day) {

      if ( this.days[day] ) {
        this.days[day] = false;
      } else {
        this.days[day] = true;
      }

      this.$emit('submit');

      e.preventDefault();
      return false;
    }

  }


};
</script>

<style>

</style>