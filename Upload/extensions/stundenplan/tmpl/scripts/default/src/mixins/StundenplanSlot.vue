<template>

  <div class="" @click="handlerClick" >
    <div class="flex-row" >
      <div class="flex-b-50 text-bold">
        <div v-if="toggleVplan && vplan && vplan.fach_neu && vplan.fach_neu != item.subject" class="text-red margin-r-s">{{ vplan.fach_neu }}</div>
        <div v-if="item.subject" :class="{'text-line text-grey': toggleVplan && vplan && vplan.fach_neu != item.subject }">{{ item.subject }}</div>
      </div>
      <div class="flex-b-50 ">
        <div v-if="toggleVplan && vplan && vplan.klasse && vplan.klasse != item.grade" class="text-red margin-r-s">{{ vplan.klasse }}</div>
        <div v-if="item.grade" :class="{'text-line text-grey': toggleVplan && vplan && vplan.klasse != item.grade }">{{ item.grade }}</div>
      </div>
      <div class="flex-b-50 ">
        <div v-if="toggleVplan && vplan && vplan.user_neu && vplan.user_neu != item.teacher" class="text-red margin-r-s">{{ vplan.user_neu }}</div>
        <div v-if="item.teacher" class="text-grey" :class="{'text-line text-grey': toggleVplan && vplan && vplan.user_neu != item.teacher }"> {{ item.teacher }}</div>
      </div>
      <div class="flex-b-50">
        <div v-if="toggleVplan && vplan && vplan.raum && vplan.raum != item.room" class="text-red margin-r-s">{{ vplan.raum }}</div>
        <div v-if="item.room" class="text-grey" :class="{'text-line text-grey': toggleVplan && vplan && vplan.raum != item.room }">{{ item.room }}</div>
      </div>
    </div>

    <SlotModal :acl="acl" :show="showModal" :data="item" :vplan="vplan" :toggleVplan="toggleVplan"></SlotModal>

  </div>

</template>

<script>

import SlotModal from './SlotModal.vue'


export default {
  components: {
    SlotModal
  },
  data() {
    return {
      
      showModal: false
    };
  },
  props: {
    item: Array,
    vplan: Array,
    toggleVplan: Number,
    acl: Array
  },
  created: function () {

  },
  watch: {
    
  },
  methods: {

    handlerClick() {
      this.showModal = !this.showModal;
    }

  }


};
</script>

<style>

</style>