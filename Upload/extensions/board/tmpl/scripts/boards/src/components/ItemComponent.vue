<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
        </button>
      </div>

    </div>

    <div class="">
      <h2>{{ item.title }}</h2>

      <div v-if="item.items" class="flex-row">

        <div v-bind:key="index" v-for="(child, index) in item.items" class="si-box margin-r-m" style="width: 25vw">

          <img v-if="child.coverURL" :src="child.coverURL" width="100%" height="auto"/>

          <h3>{{ child.title }}</h3>
          <div v-if="child.textHTML" v-html="child.textHTML" class="padding-t-m" :style="boxStyle(child)"></div>

          <div class="margin-t-m">
            <button v-if="child.textHTML && !child.open && child.textHTML.length > 200" @click="handlerReadMore(child)" class="si-btn si-btn-green "><i
                class="fa fa-arrow-right"></i> Weiterlesen
            </button>
          </div>

          <div v-if="child.pdfURL" class="margin-t-m">
            <a :href="child.pdfURL" class="si-btn "><i class="fa fa-download"></i> Download PDF</a>
          </div>

          <div v-if="child.url" class="">
            <a class="si-btn" :href="child.url" target="_blank"><i class="fas fa-external-link-alt"></i> Website</a>
          </div>

          <div class="flex-row flex-end">
            <div v-if="child.enddate" class="padding-t-m text-grey text-right margin-r-l">Bis {{ child.enddate }}</div>
            <button v-if="!child.read" @click="handlerReadDone(child)" class="si-btn si-btn-border si-btn-icon"><i class="fa fa-check"></i></button>
            <button v-if="child.read == true"  class="si-btn si-btn-off si-btn-icon text-green"><i class="fas fa-check-double"></i></button>
          </div>
        </div>

      </div>

    </div>

  </div>

</template>

<script>


export default {
  name: 'ItemComponent',
  components: {},
  data() {
    return {
      width: 10,
      form: {}
    };
  },
  props: {
    acl: Array,
    item: []
  },
  created: function () {
  },
  mounted: function () {
  },
  methods: {

    boxStyle: function (child) {
      if (!child.open) {
        return 'height:14.5rem; overflow: hidden;';
      }
      return '';
    },
    handlerReadMore: function (child) {
      child.open = true;
    },
    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },
    handlerReadDone: function (child) {
      this.$bus.$emit('page--read', {
        item: child
      });
      child.read = true;
    },



  }


};
</script>

<style>

</style>