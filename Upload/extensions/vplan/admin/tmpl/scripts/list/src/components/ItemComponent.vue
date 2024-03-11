<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
      <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
    </div>
    <div class=""></div>
  </div>

    <div class="si-form" v-if="form">
      <ul>
        <li :class="required" class="">
          <label>Titel</label>
          <input type="text" v-model="form.title" readonly>
        </li>
        <li :class="required" class="">
          <label>Kalender</label>
          <div>
            <div v-if="item.calender" class="si-btn" :style="'background-color:'+item.calender.color">{{ item.calender.title }}</div>
          </div>
        </li>
        <li :class="required" class="">
          <label>Datum Start</label>
          <input type="text" v-model="form.dateStart" readonly>
        </li>
        <li :class="required" class="">
          <label>Datum Ende</label>
          <input type="text" v-model="form.dateEnd" readonly>
        </li>
        <li :class="required" class="">
          <label>Uhrzeit Start</label>
          <input type="text" v-model="form.timeStart" readonly>
        </li>
        <li :class="required" class="">
          <label>Uhrzeit Ende</label>
          <input type="text" v-model="form.timeEnd" readonly>
        </li>
        <li :class="required" class="">
          <label>Ort</label>
          <input type="text" v-model="form.place" readonly>
        </li>
        <li :class="required" class="">
          <label>Notiz</label>
          <input type="text" v-model="form.comment" readonly>
        </li>
      </ul>
    </div>

  </div>

</template>

<script>



export default {
  name: 'ItemComponent',
  components: {

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
    item: [],
    randFolder: String
  },
  created: function () {

    this.form = this.item;

  },
  watch: {
    item: function(newVal)  {
      this.form = newVal;

      //console.log('wwww')
    }
  },
  methods: {

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