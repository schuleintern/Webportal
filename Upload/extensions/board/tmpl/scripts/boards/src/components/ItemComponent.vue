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
            <button v-if="child.textHTML && !child.open" @click="handlerReadMore(child)" class="si-btn si-btn-green "><i
                class="fa fa-arrow-right"></i> Weiterlesen
            </button>
          </div>
          <div v-if="child.pdfURL" class="margin-t-m">
            <a :href="child.pdfURL" class="si-btn "><i class="fa fa-download"></i> Download PDF</a>
          </div>

          <div v-if="child.enddate" class="padding-t-m text-grey text-right">Bis {{ child.enddate }}</div>

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


  }


};
</script>

<style>

</style>