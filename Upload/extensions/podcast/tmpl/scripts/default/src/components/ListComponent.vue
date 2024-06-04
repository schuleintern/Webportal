<template>
  <div class="">

    <!--<div v-on:click="handlerSort('id')" class="curser-sort">Sort ID</div>-->

    <div v-if="first" class="si-box flex-row bg-white flex-b-30 margin-r-m">

      <div v-if="first.cover" class="flex-1 image width-100p height-20rem margin-r-m"
        :style="'background-image: url(' + apiURL + '/getFile/' + first.id + '/' + first.cover + ')'"></div>

      <div class="flex-2">

        <!--<a :href="'#item'+item.id" v-on:click="handlerOpen(item)">{{ item.title }}</a>-->
        <h1>{{ first.title }}</h1>
        <p>{{ first.desc }}</p>
        <div class="text-small">{{ first.author }} - {{ first.count }}</div>

        <br>
        

        <AudioPlayer v-if="first.file" :option="{
          src: apiURL + '/getFile/' + first.id + '/' + first.file
        }" v-on:play="handlerPlay(first)" />


      </div>
    </div>
    <div v-else>
      <div class="padding-m"><i>- Noch keine Inhalte vorhanden -</i></div>
    </div>


    <ul class="list noListStyle flex-row" v-if="sortList && sortList.length >= 1">
      <li v-bind:key="index" v-for="(item, index) in  sortList" class="si-box bg-white flex-b-30 margin-r-m">

        <div v-if="item.cover" class="image width-100p height-20rem"
          :style="'background-image: url(' + apiURL + '/getFile/' + item.id + '/' + item.cover + ')'"></div>


        <!--<a :href="'#item'+item.id" v-on:click="handlerOpen(item)">{{ item.title }}</a>-->
        <h3>{{ item.title }}</h3>
        <p>{{ item.desc }}</p>


        <audio v-if="item.file" v-on:play="handlerPlay(item)" :src="apiURL + '/getFile/' + item.id + '/' + item.file"
          controls></audio>

        <div class="text-small">{{ item.author }} - {{ item.count }}</div>
        <div>Count: {{ item.count }}</div>

      </li>
    </ul>


  </div>
</template>

<script>

import AudioPlayer from 'vue3-audio-player'
import 'vue3-audio-player/dist/style.css'

export default {
  name: 'ListComponent',
  components: {
    AudioPlayer
  },
  data() {
    return {

      sort: {
        column: 'title',
        order: true
      },
      searchColumns: ['id', 'title'],
      searchString: '',
      apiURL: window.globals.apiURL,
      fileURL: window.globals.fileURL,

    };
  },
  props: {
    acl: Object,
    list: Array,
    first: Array
  },
  computed: {
    sortList: function () {
      if (this.list) {
        let data = this.list;
        if (data.length > 0) {

          // SUCHE
          if (this.searchString != '') {
            let split = this.searchString.toLowerCase().split(' ');
            var search_temp = [];
            var search_result = [];
            this.searchColumns.forEach(function (col) {
              search_temp = data.filter((item) => {
                return split.every(v => item[col].toLowerCase().includes(v));
              });
              if (search_temp.length > 0) {
                search_result = Object.assign(search_result, search_temp);
              }
            });
            data = search_result;
          }

          // SORTIERUNG
          if (this.sort.column) {
            if (this.sort.order) {
              return data.sort((a, b) => a[this.sort.column].localeCompare(b[this.sort.column]))
            } else {
              return data.sort((a, b) => b[this.sort.column].localeCompare(a[this.sort.column]))
            }
          }

          return data;
        }
      }
      return [];
    }

  },
  created: function () {


  },
  methods: {

    handlerOpen(item) {

      this.$bus.$emit('page--open', {
        page: 'item',
        item: item
      });

    },
    handlerSort: function (column) {
      if (column) {
        this.sort.column = column;
        if (this.sort.order) {
          this.sort.order = false;
        } else {
          this.sort.order = true;
        }
      }
    },
    handlerPlay: function (item) {
      //console.log('play', item);
      this.$bus.$emit('item--setPlayed', {
        item: item
      });
    }

  }


};
</script>

<style>
.image {
  background-repeat: no-repeat;
  background-position: center;
  background-size: contain;
}

p {
  white-space: normal;
}
</style>