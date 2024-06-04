<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
        </button>
        <button class="si-btn" v-on:click="handlerSubmit"><i class="fas fa-save"></i> Speichern</button>
      </div>
    </div>

    <div class="width-70vw min_height_70">
      <div class="si-form ">
        <ul class="">

          <li>
            <label>Title</label>
            <input type="text" v-model="form.title" required >
          </li>
          <li>
            <label>Start</label>
            <VueDatePicker
                required :previewFormat="format" :format="format" v-model="form.start" modelType="yyyy-MM-dd"
                :enableTimePicker="false" locale="de" cancelText="Abbrechen"
                selectText="Ok"
                :monthChangeOnScroll="false"
                class=""></VueDatePicker>
          </li>
          <li>
            <label>Ende</label>
            <VueDatePicker
                required :previewFormat="format" :format="format" v-model="form.end" modelType="yyyy-MM-dd"
                :enableTimePicker="false" locale="de" cancelText="Abbrechen"
                selectText="Ok"
                :monthChangeOnScroll="false"
                class=""></VueDatePicker>
          </li>
          <li>
            <User v-if="form.createdBy" :data="form.createdBy"></User>
          </li>



        </ul>

      </div>
    </div>

  </div>

</template>

<script>

import User from '../mixins/User.vue'

export default {
  name: 'ItemComponent',
  components: {
   User
  },
  setup() {

    const format = (val) => {
      let startDate = val[0] || val;
      let startDateDay = String(startDate.getDate()).padStart(2, '0')
      let startDateMonth = String(startDate.getMonth() + 1).padStart(2, '0')
      let startDateYear = String(startDate.getFullYear());

      return `${startDateYear}-${startDateMonth}-${startDateDay}`
    }

    const formatTime = (val) => {

      let hours = String(val.hours).padStart(2, '0')
      let minutes = String(val.minutes).padStart(2, '0')

      return `${hours}:${minutes}`
    }

    return {
      format,
      formatTime
    }
  },
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false
    };
  },
  props: {
    acl: Array,
    item: []
  },
  created: function () {
    this.form = this.item;
  },
  methods: {

    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },

    handlerSubmit: function () {

      if (!this.item.title) {
        return false;
      }
      this.deleteItem = false;
      this.$bus.$emit('item--submit', {
        item: this.form
      });


    },


  }


};
</script>

<style>

</style>