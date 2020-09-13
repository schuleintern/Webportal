<template>
  <div class="calendar-list">
    <ul class="noListStyle flex-row">
      <li v-bind:key="i" v-for="(item, i) in kalender" >
        <button v-on:click="handlerClickKalender(item.kalenderID)"
          v-bind:style="styleButton(item.kalenderID, item.kalenderColor)"
          class="btn margin-r-xs">{{item.kalenderName}}</button>
      </li>
    </ul>
  </div>
</template>


<script>

export default {
  name: 'Calendarlist',
  props: {
    kalender: Array
  },
  data(){
    return{
      selected: []
    }
  },
  created: function () {

    var that = this;

    EventBus.$on('list--preselected', data => {
      if (data.selected[0]) {
        that.selected = data.selected;
      }
    });

  },
  computed: {
   
  },
  methods: {
    styleButton: function (kalenderID, kalenderColor) {

      if(this.selected.indexOf(parseInt(kalenderID)) > -1) {
        return { backgroundColor: kalenderColor };
      } else {
        return { borderLeft: '5px solid '+kalenderColor };
      }
     
    },
    activeKalender: function (kalenderID) {

      if(this.selected.indexOf(parseInt(kalenderID)) > -1) {
        return true;
      } 
      return false;

    },
    handlerClickKalender: function (kalenderID) {

      kalenderID = parseInt(kalenderID);
      if (kalenderID) {
        let index = this.selected.indexOf(kalenderID);
        if (index > -1) {
          this.selected.splice(index, 1);
        } else {
          this.selected.push(kalenderID);
        }
      }
      EventBus.$emit('list--selected', {'selected': this.selected});
      
    }

  }
}
</script>
