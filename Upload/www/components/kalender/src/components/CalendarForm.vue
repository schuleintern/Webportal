<template>
  <div class="form-modal" v-on:click.self="handlerCloseModal" v-show="modalActive">
    <div class="form form-style-2 form-modal-content">
      <div class="form-modal-close"v-on:click="handlerCloseModal"><i class="fa fa-times"></i></div>

      <div v-if="formErrors.length" class="form-modal-error">
        <b>Folgende Fehler sind aufgetreten:</b>
        <ul>
          <li v-for="error in formErrors">{{ error }}</li>
        </ul>
      </div>

      <h3>{{form.day}}</h3>
      <input type="hidden" v-model="form.id"  />
      <input type="hidden" v-model="form.day"  />
      <input type="text" v-model="form.title" placeholder="Titel"  />

      <div class="flex-row">
        <div class="flex-1">
          <h4>Kalender:</h4>
          <ul class="noListStyle">
            <li v-bind:key="i" v-for="(item, i) in kalender" >
              <button v-on:click="handlerClickKalender(item.kalenderID)"
              v-bind:style="styleButton(item.kalenderID, item.kalenderColor)"
              class="btn">{{item.kalenderName}}</button>
            </li>
          </ul>
        </div>
        <div class="flex-1">
          <ul class="noListStyle">
            <li>
              <label>Start Clock:</label>
              <!-- <input type="hidden" v-model="form.start" /> -->
              <vue-timepicker v-model="form.start" format="HH:mm" :minute-interval="5"></vue-timepicker>
            </li>
            <li>
              <label>End Clock:</label>
              <!-- <input type="hidden" v-model="form.end"  /> -->
              <vue-timepicker v-model="form.end" format="HH:mm" :minute-interval="5"></vue-timepicker>
            </li>
            <li>
              Place:
              <input type="text" v-model="form.place" />
            </li>
            <li>
              Comment:
              <textarea v-model="form.comment"></textarea>
            </li>
          </ul>
        </div>
      </div>

      <button v-on:click="handlerClickAddEintrag">Hinzufügen</button>


      
    </div>
  </div>
</template>


<script>

import VueTimepicker from 'vue2-timepicker'

export default {
  name: 'CalendarForm',
  components: {
    VueTimepicker
  },
  props: {
    formErrors: Array,
    kalender: Array,
    calendarSelected: Array,
    acl: Array
  },
  data(){
    return {
      modalActive: false,
      form: {
        calenderID: 0,
        day: '',
        start: '00:00',
        end: '00:00',
        title: '',
        place: '',
        comment: ''
      }
    }
  },
  watch:{
    calendarSelected: function () {
      if (this.calendarSelected[0]) {
        this.form.calenderID = this.calendarSelected[0];
      }
    }
  },
  created: function () {

    var that = this;
    
    EventBus.$on('eintrag--form-open', data => {


      if (!data.form.day) {
        return false;
      }

      if ( data.form.id ) {
        this.form.id = data.form.id;
      }
      if ( data.form.calenderID ) {
        this.form.calenderID = data.form.calenderID;
      }
      if ( data.form.day ) {
        this.form.day = data.form.day;
      }
      if ( data.form.start ) {
        this.form.start = that.$moment(data.form.start, 'YYYY-MM-DD HH:mm:ss', true).format('HH:mm');
      }
      if ( data.form.end ) {
        this.form.end = that.$moment(data.form.end, 'YYYY-MM-DD HH:mm:ss', true).format('HH:mm');
      }
      if ( data.form.title ) {
        this.form.title = data.form.title;
      }
      if ( data.form.place ) {
        this.form.place = data.form.place;
      }
      if ( data.form.comment ) {
        this.form.comment = data.form.comment;
      }

      this.modalActive = true;

    });

    EventBus.$on('eintrag--form-reset', data => {

      this.form = {
        id: 0,
        calenderID: this.form.calenderID,
        day: '',
        start: '00:00',
        end: '00:00',
        title: '',
        place: '',
        comment: ''
      };

    });

  },
  computed: {
   
  },
  methods: {
    
    styleButton: function (kalenderID, kalenderColor) {

      if(this.form.calenderID == kalenderID) {
        return { backgroundColor: kalenderColor, borderLeft: '5px solid '+kalenderColor };
      } else {
        return { borderLeft: '5px solid '+kalenderColor };
      }
     
    },

    handlerClickKalender: function (kalenderID) {

      if (kalenderID) {
        this.form.calenderID = kalenderID;
      }
      return false;

    },

    handlerClickAddEintrag: function () {

      if (this.form.day != ''
        && this.form.title != ''
        && this.form.calenderID != '' ) {

        var values = {
          id: this.form.id,
          calenderID: this.form.calenderID,
          start: '00:00',
          end: '00:00',
          title: this.form.title,
          place: this.form.place,
          comment: this.form.comment
        };

        if (this.form.start == '' || this.form.start == '00:00') {
          values.start = '00:00';
        } else {
          values.start = this.form.start;
        }

        if (this.form.end == '' || this.form.end == '00:00') {
          values.end = values.start;
        } else {
          values.end = this.form.end;
        }

        values.start = this.form.day+' '+values.start;
        values.end = this.form.day+' '+values.end;

        EventBus.$emit('eintrag--submit', {
          form: values
        });

        this.modalActive = false;
      
      }

      this.formErrors = [];

      if ( this.form.day == '') {
        this.formErrors.push('Day required.');
      }
      if (this.form.title == '') {
        this.formErrors.push('Title required.');
      }
      if (this.form.calenderID == '') {
        this.formErrors.push('Kalender required.');
      }
      
      return false;
    },

    handlerCloseModal: function () {
      this.modalActive = false;
    }

  }
}
</script>


<!-- Add "scoped" attribute to limit CSS to this component only -->
