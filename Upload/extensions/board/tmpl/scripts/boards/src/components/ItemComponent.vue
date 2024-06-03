<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
        </button>
      </div>

    </div>

    <div class="width-70vw">
      <h2>{{ item.title }}</h2>

      <div v-if="item.items" class="flex-row">
        <div v-bind:key="index" v-for="(child, index) in  item.items" class="si-box flex-b-30 margin-r-m">
          <h3>{{ child.title }}</h3>

          <div v-if="child.coverURL">
            <img v-if="!child.coverOff" :src="child.coverURL" :width="width+'px'" @click="handlerCover(child)" />
            <VuePdfEmbed v-if="child.pdfURL && child.coverOff" annotation-layer text-layer :source="child.pdfURL" :width="width"  />
          </div>
          <div v-else>
            <VuePdfEmbed v-if="child.pdfURL" annotation-layer text-layer :source="child.pdfURL" :width="width"  />
          </div>

          <div v-if="child.textHTML" v-html="child.textHTML" class="padding-t-m"></div>
          <div v-if="child.enddate"  class="padding-t-m text-grey text-right">Bis {{child.enddate}}</div>

        </div>
      </div>

    </div>

  </div>

</template>

<script>

import VuePdfEmbed from 'vue-pdf-embed'

// essential styles
import 'vue-pdf-embed/dist/style/index.css'

// optional styles
import 'vue-pdf-embed/dist/style/annotationLayer.css'
import 'vue-pdf-embed/dist/style/textLayer.css'


export default {
  name: 'ItemComponent',
  components: {
    VuePdfEmbed
  },
  data() {
    return {
      width: 400,
      form: {}
    };
  },
  props: {
    acl: Array,
    item: []
  },
  created: function () {

  },
  methods: {

    handlerCover: function (item) {
      if (item.pdf) {
        item.coverOff = true;
      }
    },
    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },


  }


};
</script>

<style>

</style>