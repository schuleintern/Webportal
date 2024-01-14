<template>

  <div class="">

    <table class="si-table si-table-style-firstLeft">
      <thead>
        <tr>
          <th width="30%" v-on:click="handlerSort('title')" class="curser">Title</th>
          <th>Fields</th>
          <th>Template</th>
          <th>Use</th>
          <th v-on:click="handlerSort('id')" class="curser">ID</th>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  sortedArray">
          <td class=""><a v-on:click="handlerClick(item)">{{item.title}}</a></td>
          <td class=""><a v-on:click="handlerClickFields(item)">Edit</a></td>
          <td class="">{{item.template}}</td>
          <td class=""><a :href="'index.php?page=ext_cck&view=form&id='+item.id">Form</a></td>
          <td class="">{{item.id}}</td>
        </tr>
      </tbody>
    </table>


  </div>

</template>


<script>

import User from './../mixins/User.vue'

export default {
  components: {
    User
  },
  name: 'List',
  props: {
    items: Array
  },
  data(){
    return {
      sort: {
        column: false,
        order: true
      }
    }
  },
  computed: {
    sortedArray: function() {

      if (this.sort.column) {
        if (this.sort.order) {
          return this.items.sort((a, b) => a[this.sort.column].localeCompare(b[this.sort.column]))
        } else {
          return this.items.sort((a, b) => b[this.sort.column].localeCompare(a[this.sort.column]))
        }
      }
      return this.items;
    }
  },
  created: function () {
  },
  mounted() {
  },
  methods: {

    handlerClickFields: function (item) {
      EventBus.$emit('modal-item--open', {
        item: item
      });
    },
    handlerClick: function (item) {
      EventBus.$emit('modal-form--open', {
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
    }
  }
}
</script>
