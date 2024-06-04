<template>

  <div class="" v-if="acl.read == 1">

    <div class="flex-row">
      <div class="flex-1 flex-row flex-space-between">
        <a href="#list" class="si-btn si-btn-light" v-on:click="handlerBack"><i class="fa fa-angle-left"></i> Zurück</a>
        <a :href="'#'+item.id" class="si-btn si-btn-light si-btn-icon" v-on:click="handlerOpenForm(item)"><i
            class="fa fa-pen"></i> Bearbeiten</a>
      </div>
    </div>

    <h2 class="padding-b-l">{{ item.title }}</h2>

    <p>{{ item.info }}</p>
    <div>{{ item.endDate }}</div>

    <div v-bind:key="index" v-for="(item, index) in  item.members">
      <User v-bind:data="item"></User>
    </div>

    <hr>


    <Folders v-bind:acl="acl" v-bind:item="item"></Folders>


  </div>

</template>


<script>

import User from "../mixins/User.vue";

import Folders from "./Folders.vue";

export default {
  components: {
    User, Folders
  },
  name: 'Item',
  props: {
    item: Object,
    acl: Object
  },
  data() {
    return {}
  },
  computed: {},
  watch: {},
  created: function () {
  },
  mounted() {

  },
  methods: {

    handlerBack: function () {
      this.$bus.$emit('page--open', {
        back: true
      });
    },
    handlerOpenForm: function (item) {
      this.$bus.$emit('page--open', {
        page: 'form',
        item: item
      });
    },

  }
}
</script>
