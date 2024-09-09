<template>

  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">
    <div class="si-modal-box">
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content si-form ">
        <ul>
          <li style="padding-bottom: 0;padding-top: 0" class="flex-row" >
            <div class="si-btn-multiple tabs-head  flex-1 margin-r-l">
              <button class="si-btn" :class="{'si-btn-active':openTab=='lnw'}"
                      @click="handlerOpenTab('lnw')"><i class="fas fa-graduation-cap"></i> Leistungserhebung</button>
              <button class="si-btn" :class="{'si-btn-active':openTab=='event'}"
                      @click="handlerOpenTab('event')"><i class="fas fa-calendar-plus"></i> Termin</button>
            </div>

            <div class="flex-1 margin-l-l">
              <Datepicker
                  required :previewFormat="format" :format="format" v-model="form.startDay" modelType="yyyy-MM-dd"
                  :enableTimePicker="false" locale="de" cancelText="Abbrechen"
                  selectText="Ok"
                  :monthChangeOnScroll="false"
                  placeholder="Datum"
                  class=""></Datepicker>
            </div>
          </li>

          <li  v-if="openTab == 'event'">
            <label>Titel</label>
            <input type="text" v-model="form.title" placeholder="Event Titel" class=""/>
          </li>
        </ul>
        <div class="tabs">

          <div v-if="openTab == 'lnw'"  class="tab flex-row">
            <div class="flex-2 margin-r-l">
              <div class="flex-row margin-t-l padding-l-m">
                <div class="flex-1 padding-l-m">
                  <h4 class="margin-b-m">Kalender wählen:</h4>
                  <div v-bind:key="i" v-for="(item, i) in calendars" class="margin-r-s margin-b-s blockInline">
                    <button v-on:click="handlerClickKalender(item.id)"
                            v-bind:style="styleButton(item.id, item.color)"
                            class="si-btn si-btn-toggle-off " :class="{'si-btn-toggle-on': activeButton(item.id)}"
                            v-if="checkAcl(item.acl)">{{ item.title }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="flex-3">
              <ul class="">
                <li>
                  <label>Stunde</label>
                  <FormMultiSelect :input="form.stunde" :options="stunden" @submit="triggerStunden"></FormMultiSelect>
                </li>
                <li>
                  <label>Art</label>
                  <FormSelect :input="form.art" :options="lnw_options" @submit="triggerArt"></FormSelect>
                </li>
                <li>
                  <label>Fach</label>
                  <FormSelect :input="form.fach" :options="lnw_fach" @submit="triggerFach"></FormSelect>
                </li>
                <li>
                  <label>Lehrer*in</label>
                  <FormSelect :input="form.teacher" :options="lnw_teachers" @submit="triggerTeacher"></FormSelect>
                </li>
                <li>
                  <label>Raum</label>
                  <input type="text" v-model="form.place"/>
                </li>
                <li>
                  <label>Hinweis</label>
                  <textarea v-model="form.comment"></textarea>
                </li>
              </ul>
            </div>
          </div>

          <div v-if="openTab == 'event'"  class="tab flex-row">
            <div class="flex-2 margin-r-l">
              <div class="flex-row margin-t-l padding-l-m">
                <div class="flex-1 padding-l-m">
                  <h4 class="margin-b-m">Kalender wählen:</h4>
                  <div v-bind:key="i" v-for="(item, i) in calendars" class="margin-r-s margin-b-s blockInline">
                    <button v-on:click="handlerClickKalender(item.id)"
                            v-bind:style="styleButton(item.id, item.color)"
                            class="si-btn si-btn-toggle-off" :class="{'si-btn-toggle-on': activeButton(item.id)}"
                            v-show="checkAcl(item.acl)">{{ item.title }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="flex-3">
              <ul class="">
                <li>
                  <label>Bis</label>
                  <Datepicker
                      required :previewFormat="format" :format="format" v-model="form.endDay" modelType="yyyy-MM-dd"
                      :enableTimePicker="false" locale="de" cancelText="Abbrechen"
                      selectText="Ok"
                      :monthChangeOnScroll="false"></Datepicker>


                </li>
                <li>
                  <label>Stunde</label>
                  <FormMultiSelect :input="form.stunde" :options="stunden" @submit="triggerStunden"></FormMultiSelect>
                </li>
                <li>
                  <label>Uhrzeit Start</label>
                  <Datepicker
                      required timePicker :format="formatTime" v-model="form.startTime" modelType="HH:ii"
                      locale="de" cancelText="Abbrechen"
                      selectText="Ok"></Datepicker>
                </li>
                <li>
                  <label>Uhrzeit Ende</label>

                  <Datepicker
                      required timePicker :format="formatTime" v-model="form.endTime" modelType="HH:ii"
                      locale="de" cancelText="Abbrechen"
                      selectText="Ok"></Datepicker>

                </li>
                <li style="flex-direction: row;">
                  <FormToggle :input="form.repeat" class="flex-1"
                              @change="handlerToggleChange($event, form,'repeat')"></FormToggle>
                  <label class="flex-3 text-small padding-l-m padding-t-s"  style="display: inline-block; width: 80%">Termin wiederholen</label>
                </li>
                <li v-if="form.repeat == 1" class="padding-b-m padding-t-s">
                  <div class="flex-row">
                    <input class="flex-1" type="radio" name="repeat_type" id="repeat_type_week" value="week" v-model="form.repeat_type">
                    <label class="flex-1 padding-l-s padding-t-s" for="repeat_type_week">Wöchentlich (jeden {{formatDate(form.startDay,'dddd')}})</label>
                  </div>
                  <div class="flex-row">
                    <input class="flex-1" type="radio" name="repeat_type" id="repeat_type_month" value="month" v-model="form.repeat_type">
                    <label class="padding-l-s padding-t-s" for="repeat_type_month">Monatlich (jeden {{formatDate(form.startDay,'DD.')}})</label>
                  </div>
                  <div class="flex-row">
                    <input class="flex-1" type="radio" name="repeat_type" id="repeat_type_year" value="year" v-model="form.repeat_type">
                    <label class="padding-l-s padding-t-s" for="repeat_type_year">Jährlich (jeden {{formatDate(form.startDay,'DD.MM.')}})</label>
                  </div>
                </li>
                <li>
                  <label>Ort</label>
                  <input type="text" v-model="form.place"/>
                </li>
                <li>
                  <label>Notiz</label>
                  <textarea v-model="form.comment"></textarea>
                </li>

              </ul>
              <input type="hidden" v-model="form.status"/>
              <input type="hidden" v-model="form.id"/>
              <input type="hidden" v-model="form.startDay"/>
            </div>

          </div>


        </div>
        <br>
        <button v-on:click="handlerClickAddEintrag" class="si-btn width-100p"><i class="fa fa-save"></i> <span v-if="form.status">Vorschlag </span>Speichern</button>
      </div>

    </div>
  </div>


</template>

<script>

import {onMounted, ref} from "vue";
import FormToggle from "../mixins/FormToggle";
import FormSelect from "../mixins/FormSelect";
import FormMultiSelect from "../mixins/FormMultiSelect";

export default {
  name: 'CalendarForm',
  components: {
    FormSelect,
    FormToggle,
    FormMultiSelect
  },
  setup() {

    const format = (val) => {
      let startDate = val[0] || val;
      let endDate = val[1];

      let startDateDay = String(startDate.getDate()).padStart(2, '0')
      let startDateMonth = String(startDate.getMonth() + 1).padStart(2, '0')
      let startDateYear = String(startDate.getFullYear());
      if (endDate) {
        let endDateDay = String(endDate.getDate()).padStart(2, '0')
        let endDateMonth = String(endDate.getMonth() + 1).padStart(2, '0')
        let endDateYear = String(endDate.getFullYear());
        return `${startDateDay}.${startDateMonth}.${startDateYear} - ${endDateDay}.${endDateMonth}.${endDateYear}`
      } else {
        return `${startDateDay}.${startDateMonth}.${startDateYear}`

      }
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
      open: false,
      form: [],
      stunden: window.globals.stunden,
      lnw_options: window.globals.lnw_options,
      lnw_fach: window.globals.fach_options,
      lnw_teachers: window.globals.teachers_options,
      openTab: false
    };
  },
  props: {
    calendars: Array
  },
  created: function () {


    var that = this;
    this.$bus.$on('event-form--open', data => {
      if (data.form) {
        that.form = data.form;

        that.form.calenderID = [String(that.form.calenderID)];
        if (that.form.startTime) {
          let dateStart = that.form.startTime.split(':');
          that.form.startTime = { "hours": dateStart[0], "minutes": dateStart[1], "seconds": 0 };
        }
        if (that.form.endTime) {
          let dateEnd = that.form.endTime.split(':');
          that.form.endTime = { "hours": dateEnd[0], "minutes": dateEnd[1], "seconds": 0 };
        }
      }

      if (that.form.typ) {
        this.handlerOpenTab(that.form.typ);
      } else {
        this.handlerOpenTab('lnw');
      }
      that.open = true;

    });
    this.$bus.$on('event-form--close', data => {
      that.open = false;
      that.form = [];
    });

  },
  methods: {
    handlerOpenTab(tab) {
      if (tab) {
        this.openTab = tab;
        this.form.typ = tab;
      }
    },
    triggerArt(val) {
      this.form.art = val.value;
    },
    triggerFach(val) {
      this.form.fach = val.value;
    },
    triggerTeacher(val) {
      this.form.teacher = val.value;
    },
    triggerStunden(val) {
      this.form.stunde = val.value;
    },
    handlerToggleChange(event, item, elm) {
      item[elm] = event.value;
    },
    formatDate:function(date,format) {
      return this.$dayjs(date).format(format);
    },
    handlerClickKalender(id) {
      if (!this.form.calenderID) {
        this.form.calenderID = [];
      }
      if (id && this.form.calenderID) {
        //this.form.calenderID = id;
        if(!this.form.calenderID.includes(id)){
          this.form.calenderID.push(id);
        }else{
          this.form.calenderID.splice(this.form.calenderID.indexOf(id), 1);
        }
      }
      return false;
    },
    checkAcl(acl) {

      //console.log(acl);

      if (acl && acl.rights) {
        //console.log(acl);
        if ( parseInt(acl.rights.write) === 1) {
          return true;
        }
      }
      return false;
    },
    activeButton(id) {
      /*
      if (this.form.calenderID == id) {
        return true;
      }
      */

      if(this.form.calenderID && Array.isArray(this.form.calenderID) && this.form.calenderID.includes(id)){
        return true;
      }
      return false;
    },
    styleButton(id, color) {

      /*
      if (this.form.calenderID == id) {
        if (color) {
          return {backgroundColor: color, borderColor: color};
        }

      } else {
        return {borderLeft: '5px solid ' + color};
      }
      */

      if(this.form.calenderID && Array.isArray(this.form.calenderID) && this.form.calenderID.includes(id)){
        if (color) {
          return {backgroundColor: color, borderColor: color};
        }
      } else {
        return {borderLeft: '5px solid ' + color};
      }

    },
    handlerClose: function () {
      this.$bus.$emit('event-form--close');
    },
    handlerClickAddEintrag() {

/*
      var values = {
        id: this.form.id,
        calenderID: this.form.calenderID,
        startTime: this.form.startTime,
        endTime: this.form.endTime,
        title: this.form.title,
        place: this.form.place,
        comment: this.form.comment,
        startDay: this.form.startDay,
        endDay: this.form.endDay,
        repeat_type: this.form.repeat_type,
        status: this.form.status,
        stunde: this.form.stunde,
        typ: this.form.typ,
        art: this.form.art,
        fach: this.form.fach,
        teacher: this.form.teacher
      };
*/


      this.$bus.$emit('event-form--submit', {
        form: this.form
      });

      //this.modalActive = false;

    }
  }


};
</script>

<style>
.dp__menu {
  z-index: 999999999999999999;
}
</style>