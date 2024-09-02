<template>
  <div class="">
    <div class="date" v-if="showClock">
      <strong>
        <div v-if="eintrag.stunde" class="margin-r-s">{{eintrag.stunde}}. Stunde</div>

        <span v-if="eintrag.startTime != '00:00'">
          {{ eintrag.startTime }}
        </span>
        <span v-if="eintrag.endTime != '00:00' && eintrag.wholeDay == false">
          - {{ eintrag.endTime }}
        </span>
      </strong>
    </div>

    <div class="title">{{ eintrag.title }}</div>

    <div class="info margin-t-s flex-row text-gey text-small" v-if="eintrag.place || eintrag.comment">
      <div v-if="eintrag.place" class="flex-1"><i class="fas fa-map-marker-alt margin-r-xs"></i>
        {{ eintrag.place }}
      </div>
      <div v-if="eintrag.comment" class="margin-t-s"><i class="fas fa-comment margin-r-s"></i> <span
          v-html="eintrag.comment"></span></div>
    </div>
  </div>
</template>

<script>

export default {
  data() {
    return {};
  },
  props: {
    eintrag: Object,
    showClock: Boolean
  },
  created: function () {

  },
  methods: {}


};
</script>

<style>

</style>