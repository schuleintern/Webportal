<template>

  <div class="si-form">
    <ul class="">
      <li :class="required">
        <label class="text-bold">Titel</label>
        <input type="text"  v-model="form.title" maxlength="20" />
      </li>
      <li :class="required">
        <label>Tag</label>
        <select v-model="form.day">
          <option v-bind:key="j" v-for="(item, j) in showDays" v-if="item == true">{{j}}</option>
        </select>
      </li>
      <li :class="required" class="flex-row">
        <div class="flex-1">
          <label>Uhrzeit</label>
          <div class="si-clock">
            <select v-model="form.timeHour" class="hours">
              <option v-bind:key="j" v-for="(item, j) in formData.hourCount" >{{formData.hourStart+j}}</option>
            </select>
            <select v-model="form.timeMinute" class="minutes">
              <option>00</option>
              <option>05</option>
              <option>10</option>
              <option>15</option>
              <option>20</option>
              <option>25</option>
              <option>30</option>
              <option>35</option>
              <option>40</option>
              <option>45</option>
              <option>50</option>
              <option>55</option>
            </select>
          </div>
        </div>
        <div class="flex flex-1">
          <label>Dauer in Minuten</label>
          <select v-model="form.duration">
            <option>05</option>
            <option>10</option>
            <option>15</option>
            <option>20</option>
            <option>25</option>
            <option>30</option>
            <option>35</option>
            <option>40</option>
            <option>45</option>
            <option>50</option>
            <option>55</option>
            <option>60</option>
          </select>
        </div>
      </li>
      <li :class="required">

        <label>Sichtbar</label>

        <div class="flex-row">
          <button
              v-if="form.typ.schueler"
              v-on:click="handlerToggle('schueler')"
              class="si-btn si-btn-active margin-r-m"><i class="fa fas fa-toggle-on"></i> Schüler</button>
          <button
              v-else-if="!form.typ.schueler"
              v-on:click="handlerToggle('schueler')"
              class="si-btn si-btn-light margin-r-m"><i class="fa fas fa-toggle-off"></i> Schüler</button>
          <button
              v-if="form.typ.eltern"
              v-on:click="handlerToggle('eltern')"
              class="si-btn si-btn-active margin-r-m"><i class="fa fas fa-toggle-on"></i> Eltern</button>
          <button
              v-else-if="!form.typ.eltern"
              v-on:click="handlerToggle('eltern')"
              class="si-btn si-btn-light margin-r-m"><i class="fa fas fa-toggle-off"></i> Eltern</button>
        </div>

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
    showDays: Array,
    formData: Array,
    item: Object
  },
  data(){
    return {

      error: false,
      required: '',

      form: {
        timeHour: '',
        timeMinute: '',
        title: '',
        day: '',
        duration: '',
        typ: {
          schueler: false,
          eltern: false
        }
      }

    }
  },
  created: function () {
  },

  watch: {
    item: {
      immediate: true,
      handler (val, oldVal) {
        //console.log(val, oldVal);

        if (val.id) {
          this.form.id = val.id;
          this.form.title = val.title;
          this.form.day = val.day;
          this.form.duration = val.duration;
          this.form.typ = val.typ;

          let time = val.time.split(':');

          this.form.timeHour = time[0];
          this.form.timeMinute = time[1];

        }
      }
    }
  },

  methods: {

    handlerToggle: function (typ) {

      //console.log(this.form.typ[typ]);

      if (typ) {
        if (this.form.typ[typ] == true) {
          this.form.typ[typ] = false;
        } else {
          this.form.typ[typ] = true;
        }
      }

    },
    submitForm: function () {
      var that = this;
      //this.form.date = this.$date(this.form.date).format('YYYY-MM-DD')

      if (!this.form.timeHour
          || !this.form.timeMinute
          || !this.form.title
          || !this.form.day
          || !this.form.duration
          || ( this.form.typ.eltern == false && this.form.typ.schueler == false )
      ) {
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
