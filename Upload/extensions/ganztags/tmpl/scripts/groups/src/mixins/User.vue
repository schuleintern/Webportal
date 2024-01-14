<template>
  <span v-if="data">

    <div v-if="size == 'line'" class="si-user si-user-line" :class="data.type" v-on:click="handlerOpen">
      {{ data.name }}
    </div>

    <div v-else class="si-user" :class="data.type" v-on:click="handlerOpen">
        <div class="avatar">
          <img :src="data.avatar" alt="" title=""/>
        </div>
        <div class="info">
          <div class="top">{{ data.vorname }}</div>
          <div class="bottom">
            <span class="name">{{ data.nachname }}</span>
            <span class="klasse">{{ data.klasse }}</span>
          </div>
        </div>
    </div>

    <div class="si-user--infoBox">
      <UserModal v-bind:data="infoBox" @close="handlerModalClose"></UserModal>
    </div>

  </span>

</template>

<script>

import UserModal from './UserModal.vue'

export default {
  components: {
    UserModal
  },
  data() {
    return {
      infoBox: false
    };
  },
  props: {
    data: Object,
    size: String
  },
  created: function () {

  },
  methods: {

    handlerOpen: function () {

      if (this.infoBox == false) {
        this.infoBox = this.data;
      } else {
        this.infoBox = false;
      }
    },
    handlerModalClose: function () {
      this.infoBox = false;
    }

  }

};
</script>

<style>

</style>