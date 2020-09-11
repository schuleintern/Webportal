<template>
    <div class="calendar-list">
{{selected}}
      <ul class="noListStyle">
        <li v-bind:key="i" v-for="(item, i) in kalender" >
          <button v-on:click="handlerClickKalender(item.kalenderID)">{{item.kalenderName}}</button>
        </li>
      </ul>

    </div>
</template>


<script>

export default {
  name: 'Calendarlist',
  props: {
    kalender: Array,
  },
  data(){
    return{
      selected: []
    }
  },
  created: function () {

  },
  computed: {
   
  },
  methods: {

    handlerClickKalender: function (kalenderID) {

      this.selected = [];
      if (kalenderID) {
        this.selected = [kalenderID];
      }
      EventBus.$emit('list--selected', {'selected': this.selected});
      

    }

  }
}
</script>


<!-- Add "scoped" attribute to limit CSS to this component only -->
