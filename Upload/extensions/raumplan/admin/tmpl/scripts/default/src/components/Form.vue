<template>

  <div class="si-form">

    <ul class="">
      <li>
        <label>Tag</label>
        <strong>{{ $date(form.date).format("DD.MM.YYYY") }}</strong>
      </li>
      <li>
        <label>Stunde</label>
        {{form.stunde}}
      </li>
      <li>
        <label>Raum</label>
        {{form.room}}
      </li>
      <li :class="required">
        <label>Klasse</label>
        <input type="text"  v-model="form.klasse" />
      </li>
      <li :class="required">
        <label>Lehrer</label>
        <input type="text"  v-model="form.lehrer" />
      </li>
      <li :class="required">
        <label>Fach</label>
        <input type="text" v-model="form.fach" />
      </li>
      <li>
        <button @click="submitForm" class="si-btn"><i class="fa fa-save"></i> Speichern</button>
      </li>
    </ul>
  </div>

</template>


<script>

export default {
  name: 'Form',
  props: {
    dates: Object,
    room: String
  },
  data(){
    return {

      error: false,
      required: '',

      form: {
        date: false,
        stunde: '',
        room: '',

        klasse: '',
        lehrer: '',
        fach: ''

      }

    }
  },
  created: function () {
  },
  mounted() {
    // access our input using template refs, then focus
  },
  watch: {
    dates: {
      immediate: true,
      handler (newVal, oldVal) {
        // do your stuff
        this.form.date = this.$date(newVal.date).format('YYYY-MM-DD');
        this.form.stunde = newVal.stunde;
        this.form.room = this.room;
        //console.log('-', newVal, oldVal);
      }
    }
  },
  methods: {

    submitForm: function () {
      var that = this;
      //this.form.date = this.$date(this.form.date).format('YYYY-MM-DD')

      if (!this.form.date
          || !this.form.stunde
          || !this.form.room
          || !this.form.klasse
          || !this.form.lehrer
          || !this.form.fach) {
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
