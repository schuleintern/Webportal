<template>
  <div>

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <grid-layout
        :layout.sync="layout"
        :col-num="colNum"
        :row-height="55"
        :is-draggable="false"
        :is-resizable="false"
        :is-mirrored="false"
        :vertical-compact="true"
        :margin="[10, 10]"
        :use-css-transforms="true"
        :auto-size="true"
        :responsive="true"
        @breakpoint-changed="breakpointChangedEvent"
    >

      <grid-item v-for="item in layout"
                 :x="item.x"
                 :y="item.y"
                 :w="item.w"
                 :h="item.h"
                 :i="item.i"
                 :key="item.i">
        <div class="dashboard-content" v-html="item.html"></div>
      </grid-item>
    </grid-layout>

  </div>
</template>

<script>

const axios = require('axios').default;

import VueGridLayout from 'vue-grid-layout';
import Error from './mixins/Error.vue';
import Spinner from './mixins/Spinner.vue';

export default {
  components: {
    Error, Spinner,
    GridLayout: VueGridLayout.GridLayout,
    GridItem: VueGridLayout.GridItem
  },
  data() {
    return {
      selfURL: globals.selfURL,
      error: false,
      loading: false,


      layout: [],
      colNum: 12,
      index: 0,
    };
  },
  created: function () {

    this.layout = globals.list;

  },
  mounted() {
    // this.$gridlayout.load();
    this.index = this.layout.length;
  },
  methods: {
    breakpointChangedEvent: function(newBreakpoint, newLayout){
      //console.log("BREAKPOINT CHANGED breakpoint=", newBreakpoint, ", layout: ", newLayout );
    }
  }

};
</script>

<style>
.box-body {
  width: 100%;
}
.vue-grid-item {
  box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 15px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px;
  border-radius: 1rem;
  padding: 1rem 1.6rem;
  margin-bottom: 0.6rem;
  margin-top: 0.6rem;
  text-overflow: ellipsis;
  white-space: nowrap;
  border: 1px solid #b7c7ce;
  color: #000;
  text-align: left;
  overflow: auto;
}


</style>