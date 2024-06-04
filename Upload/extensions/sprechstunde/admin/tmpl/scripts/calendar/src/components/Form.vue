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

      <li>
        <label>Info</label>
        <input type="text"  v-model="form.info" maxlength="250" />
      </li>
      <li>
        <button @click="submitForm" class="si-btn"><i class="fa fa-save"></i> Buchen</button>
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
    day: Object
  },
  data(){
    return {

      error: false,
      required: '',

      form: {
        date: false,
        time: '',
        duration: '',
        user_id: '',

        info: ''

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

    submitForm: function () {
      var that = this;
      //this.form.date = this.$date(this.form.date).format('YYYY-MM-DD')

      if (!this.form.date
          || !this.form.slot_id ) {
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
