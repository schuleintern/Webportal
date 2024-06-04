<template>

  <div class="si-form">

    <ul class="">
      <li :class="required">
        <label>Tag</label>
        <label class="text-bold">{{ $date(form.date).format("DD.MM.YYYY") }}</label>
      </li>
      <li>
        <label>Uhrzeit</label>
        <label class="text-bold">{{form.time}}</label>
      </li>
      <li>
        <label>Dauer</label>
        <label class="text-bold">{{form.duration}} Minuten</label>
      </li>
      <li>
        <label>Veranstalter</label>
        <User v-bind:data="slot.user"></User>
      </li>

      <li :class="required">
        <label>Ort</label>
        <div class="si-btn-multiple">
          <button v-on:click="handlerMedium('live')" class="si-btn si-btn-light" :class="{'si-btn-active' : form.medium == 'live'}" ><i class="fa fa-handshake"></i> Live</button>
          <button v-if="mediums.phone" v-on:click="handlerMedium('phone')" class="si-btn si-btn-light" :class="{'si-btn-active' : form.medium == 'phone'}" ><i class="fa fa-phone"></i> Telefon</button>
          <button v-if="mediums.viko" v-on:click="handlerMedium('viko')" class="si-btn si-btn-light" :class="{'si-btn-active' : form.medium == 'viko'}" ><i class="fa fa-video"></i> Videokonferenz</button>
        </div>
      </li>

      <li>
        <label>Info</label>
        <input type="text"  v-model="form.info" maxlength="250" />
      </li>

      <li>

        <button v-if="form.user_id != userSelf.id" @click="submitForm" class="si-btn"><i class="fa fa-save"></i> Buchen</button>

        <button v-else-if="form.user_id == userSelf.id" @click="submitForm" class="si-btn si-btn-red">
          <i class="fa fa-ban"></i> Blocken
        </button>

      </li>
    </ul>
  </div>

</template>


<script>
import User from "../mixins/User.vue";

export default {
  components: {
    User
  },
  name: 'Form',
  props: {
    slot: Object,
    day: Object,
    userSelf: Array
  },
  data(){
    return {

      mediums: globals.medium,

      error: false,
      required: '',

      form: {
        date: false,
        time: '',
        duration: '',
        user_id: '',

        info: '',
        medium: false

      }
    }
  },
  created: function () {
  },
  mounted() {
    // access our input using template refs, then focus
  },
  watch: {
    slot: {
      immediate: true,
      handler (newVal, oldVal) {

        this.form.time = newVal.time;
        this.form.duration = newVal.duration;
        this.form.user_id = newVal.user_id;
        this.form.info = newVal.info;
        this.form.slot_id = newVal.id;
        //console.log('-', newVal, oldVal);
      }
    },
    day: {
      immediate: true,
      handler (newVal, oldVal) {
        // do your stuff
        this.form.date = this.$date(newVal).format('YYYY-MM-DD');

      }
    }
  },
  methods: {

    handlerMedium: function (type) {

      this.form.medium = type;

    },
    submitForm: function () {
      var that = this;
      //this.form.date = this.$date(this.form.date).format('YYYY-MM-DD')

      if (!this.form.date
          || !this.form.slot_id
          || !this.form.medium ) {
        console.log('missing');
        this.required = 'required';
        return false;
      }

      EventBus.$emit('form--submit', {
        form: that.form
      });

    }

  }
}
</script>
