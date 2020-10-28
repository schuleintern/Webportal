<template>
  <div class="form-modal" v-on:click.self="handlerCloseModal" v-show="modalActive">
    <div class="form form-style-2 form-modal-content">
      <div class="form-modal-close" v-on:click="handlerCloseModal"><i class="fa fa-times"></i></div>

      <div class="text-small">Datum:</div>
      <!-- <div class="labelDay">{{form.startDay}}</div> -->

      <date-picker
        v-model="form.startDay"
        type="date"
        format="YYYY-MM-DD" 
        value-type="format"
        :default-value="new Date(form.startDay)"></date-picker>

      <br/>
      
      <input type="hidden" v-model="form.id"  />
      <input type="hidden" v-model="form.startDay"  />

      <br />
      <div class="text-small">Titel:</div>

      <input type="text" v-model="form.title" placeholder="Titel" class="width-100p" />

      <div class="flex-row margin-t-l">
        <div class="flex-1">
          <h4>Kalender wählen:</h4>
          <ul class="noListStyle">
            <li v-bind:key="i" v-for="(item, i) in kalender" class="margin-b-s" >
              <button v-on:click="handlerClickKalender(item.kalenderID)"
              v-bind:style="styleButton(item.kalenderID, item.kalenderColor)"
              class="btn" v-show="checkAcl(item.kalenderAcl)">{{item.kalenderName}}</button>
            </li>
          </ul>
        </div>
        <div class="flex-1">
          <ul class="noListStyle">
            <li class="margin-b-m">
              <label class="block">Bis:</label>

              <date-picker
                v-model="form.endDay"
                type="date"
                format="YYYY-MM-DD" 
                :default-value="new Date(form.startDay)"></date-picker>
              
            </li>
            <li class="margin-b-m">
              <label class="block">Uhrzeit Start:</label>
              <!-- <input type="hidden" v-model="form.start" /> -->
              <vue-timepicker v-model="form.startTime" format="HH:mm" :minute-interval="5"></vue-timepicker>
            </li>
            <li class="margin-b-m">
              <label class="block">Uhrzeit Ende:</label>
              <!-- <input type="hidden" v-model="form.end"  /> -->
              <vue-timepicker v-model="form.endTime" format="HH:mm" :minute-interval="5"></vue-timepicker>
            </li>
            <li>
              <label class="text-small block">Ort:</label>
              <input type="text" v-model="form.place" />
            </li>
            <li>
              <label class="text-small block">Notiz:</label>
              <textarea v-model="form.comment"></textarea>
            </li>
          </ul>
        </div>
      </div>

      <hr>

      <button v-on:click="handlerClickAddEintrag"
      class="btn width-100p"><i class="fa fa-save"></i>Speichern</button>


      
    </div>
  </div>
</template>



<script>

import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
import 'vue2-datepicker/locale/de';

import VueTimepicker from 'vue2-timepicker'

export default {
  name: 'CalendarForm',
  components: {
    VueTimepicker,
    DatePicker
  },
  props: {

    kalender: Array,
    calendarSelected: Array,
    acl: Object
  },
  data(){
    return {
      modalActive: false,
      form: {
        calenderID: 0,
        startDay: '',
        startTime: '00:00:00',
        endDay: '',
        endTime: '00:00:00',
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
    },
    'form.startDay': function (newValue,oldValue) {

      if (this.form.endDay) {
        var date_start = new Date(newValue);
        var date_end = new Date(this.form.endDay);
        if (date_end < date_start) {
          this.form.endDay = newValue;
        }
      }
      //console.log('change----',newValue, this.form.endDay);
    }
  },
  created: function () {

    var that = this;
    
    EventBus.$on('eintrag--form-open', data => {


      if (!data.form.startDay) {
        return false;
      }

      if ( data.form.id ) {
        this.form.id = data.form.id;
      }
      if ( data.form.calenderID ) {
        this.form.calenderID = data.form.calenderID;
      }
      if ( data.form.startDay ) {
        this.form.startDay = data.form.startDay;
      }
      if ( data.form.startTime ) {
        this.form.startTime = data.form.startTime;
      }
      if ( data.form.endDay ) {
        this.form.endDay = new Date(data.form.endDay);
      }
      if ( data.form.endTime ) {
        this.form.endTime = data.form.endTime;
      }
      if ( data.form.title ) {
        this.form.title = data.form.title;
      }
      if ( data.form.place ) {
        this.form.place = data.form.place;
      }
      if ( data.form.comment ) {
        this.form.comment = that.strippedContent(data.form.comment);
      }

      this.modalActive = true;

    });

    EventBus.$on('eintrag--form-reset', data => {

      this.form = {
        id: 0,
        calenderID: this.form.calenderID,
        startDay: '',
        startTime: '00:00:00',
        endDay: '',
        endTime: '00:00:00',
        title: '',
        place: '',
        comment: ''
      };

    });

  },
  computed: {
   formInputEndDay: function () {
     return new Date(this.form.startDay);
   }
  },
  methods: {
    
    strippedContent: function (str) {
      let regex = /(<([^>]+)>)/ig;
      return str.replace(regex, "");
    },
    checkAcl: function (acl) {
      //console.log(acl.rights.write);
      if (acl && acl.rights) {
        if ( parseInt(acl.rights.write) != 1 ) {
          return false;
        }
      }
      return true;
    },
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

      if (this.form.startDay != ''
        && this.form.title != ''
        && this.form.calenderID != '' ) {

        var values = {
          id: this.form.id,
          calenderID: this.form.calenderID,
          startTime: this.form.startTime,
          endTime: this.form.endTime,
          title: this.form.title,
          place: this.form.place,
          comment: this.form.comment,
          startDay: this.form.startDay,
          endDay: this.$moment(this.form.endDay).format('YYYY-MM-DD')
        };


        EventBus.$emit('eintrag--submit', {
          form: values
        });

        this.modalActive = false;
      
      }

      this.formErrors = [];

      if ( this.form.startDay == '') {
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
