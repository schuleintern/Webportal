<template>
  <span>
    <div v-if="size == 'line'" class="si-user si-user-line" :class="data.type" v-on:click="handlerOpen">
      {{ data.name }}
    </div>

    <div v-if="size == 'icon'" class="si-user si-user-icon" :class="data.type" v-on:click="handlerOpen">
      <i class="fa fa-user padding-r-s"></i>{{ data.name }}
    </div>

    <div v-if="size == 'avatar'" class="si-user si-user-avatar" :class="data.type" v-on:click="handlerOpen">
      <img :src="data.avatar" :alt="'Profilbild: '+data.name" :title="data.name" />
    </div>

    <div v-else class="si-user" :class="data.type" v-on:click="handlerOpen">
        <div class="avatar">
          <img :src="data.avatar" alt="" title=""/>
        </div>
        <div class="info">
          <div class="top">{{ data.nachname }}</div>
          <div class="bottom">
            <span class="name">{{ data.vorname }}</span>
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