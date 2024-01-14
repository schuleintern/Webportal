<template>

  <div class="calender">
    <AjaxError :error="error"></AjaxError>
    <AjaxSpinner :loading="loading"></AjaxSpinner>

    <CalendarForm :calendars="kalenders"></CalendarForm>
    <CalendarItem :kalenders="kalenders" :acl="acl"></CalendarItem>

    <CalendarList :kalenders="kalenders" :selectedKalenders="selectedKalenders"></CalendarList>
    <CalendarView :events="events" :calendars="kalenders" :acl="acl"></CalendarView>


  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

import CalendarList from './components/CalendarList.vue'
import CalendarView from './components/CalendarView.vue'
import CalendarForm from './components/CalendarForm.vue'
import CalendarItem from './components/CalendarItem.vue'

const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    AjaxError, AjaxSpinner,
    CalendarList, CalendarView, CalendarForm, CalendarItem
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      acl: window.globals.acl,
      error: false,
      loading: false,

      kalenders: false,
      selectedKalenders: [],

      events: []

    };
  },
  created() {

    this.loadKalenders();


    this.$bus.$on('kalenders--selected', (data) => {

      this.loadEvents(data.selected);
      //this.selectedKalenders = data.selected;
    });


    this.$bus.$on('event-form--submit', (data) => {

      if (!data.form.startDay) {
        this.error = 'Fehler beim Speichern. Day required.';
        return false;
      }
      if (!data.form.title || data.form.title == '' || data.form.title == 'undefined') {
        this.error = 'Fehler beim Speichern. Title required.';
        return false;
      }
      if (!data.form.calenderID) {
        this.error = 'Fehler beim Speichern. Kalender required.';
        return false;
      }

      if (this.acl.write != 1) {
        this.error = "Keine Schreibrechte!";
        return false;
      }

      let startTime = '';
      if (data.form.startTime) {
        startTime = data.form.startTime.hours+':'+data.form.startTime.minutes
      }
      let endTime = false;
      if (data.form.endTime) {
        endTime = data.form.endTime.hours+':'+data.form.endTime.minutes
      }

      const formData = new FormData();

      formData.append('id', data.form.id || '');
      formData.append('title', data.form.title || '');
      formData.append('kalender_id', data.form.calenderID || '');
      formData.append('dateStart', data.form.startDay || '');
      formData.append('timeStart', startTime || '');
      formData.append('dateEnd', data.form.endDay || '');
      formData.append('timeEnd', endTime || '');
      formData.append('place', data.form.place || '');
      formData.append('comment', data.form.comment || '');
      formData.append('repeat_type', data.form.repeat_type || '');

      this.loading = true;
      var that = this;
      this.axios.post(this.apiURL + '/setEvents', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                //console.log('DONE!!!!!!!!')

                that.loadEvents(that.selectedKalenders);
                that.$bus.$emit('event-form--close');
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Laden. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });


    });

    this.$bus.$on('event-item--delete', (data) => {

      if (!data.id) {
        this.error = 'Fehler beim Löschen.';
        return false;
      }

      if (this.acl.delete != 1) {
        this.error = "Keine Löschrechte!";
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.id);

      this.loading = true;
      var that = this;
      this.axios.post(this.apiURL + '/delEvent', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                //console.log('DONE!!!!!!!!')

                that.loadEvents(that.selectedKalenders);
                //that.$bus.$emit('event-form--close');
              }
            } else {
              that.error = 'Fehler beim Löschen. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Löschen. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });


    });
  },
  methods: {

    loadEvents(selected) {

      if (!selected) {
        return false
      }
      const formData = new FormData();
      formData.append('kalenders', selected);

      this.loading = true;
      var that = this;
      this.axios.post(this.apiURL + '/getEvents', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.events = that.setEvents(response.data);
                /*
                if ( !that.setEvents(response.data) ) {
                  that.error = 'Fehler beim Laden. 03';
                }
                */
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Laden. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });

    },
    setEvents(data) {

      this.loading = true;
      var ret = [];
      var that = this;

      data.forEach((eintrag) => {


        if (eintrag.repeat_type) {

          var eintrag_start = new Date(eintrag.dateStart);
          var eintrag_ende = new Date(eintrag.dateEnd);

          if (eintrag.repeat_type == 'week') {
            const ms = 1000 * 60 * 60 * 24 * 7;
            let anz = Math.round(Math.abs(eintrag_ende - eintrag_start) / ms);
            for (var i = 1; i <= anz; i++) {
              let newItem = {...eintrag};
              newItem.repeat_root = [that.$dayjs(eintrag.dateStart, 'YYYY-MM-DD', true).format('YYYY-MM-DD'),
                that.$dayjs(eintrag.dateEnd, 'YYYY-MM-DD', true).format('YYYY-MM-DD')];
              let nextStart = that.$dayjs(eintrag_start).add(i, 'week');
              newItem.dateStart = nextStart.format('YYYY-MM-DD');
              newItem.dateEnd = newItem.dateStart;
              ret.push(newItem);
            }
          }

          if (eintrag.repeat_type == 'month') {
            let anz = (
                eintrag_ende.getMonth() -
                eintrag_start.getMonth() +
                12 * (eintrag_ende.getFullYear() - eintrag_start.getFullYear())
            )
            for (let i = 1; i <= anz; i++) {
              let newItem = {...eintrag};
              newItem.repeat_root = [that.$dayjs(eintrag.dateStart, 'YYYY-MM-DD', true).format('YYYY-MM-DD'),
                that.$dayjs(eintrag.dateEnd, 'YYYY-MM-DD', true).format('YYYY-MM-DD')];
              let nextStart = that.$dayjs(eintrag_start).add(i, 'month');
              newItem.dateStart = nextStart.format('YYYY-MM-DD');
              newItem.dateEnd = newItem.dateStart;
              ret.push(newItem);
            }
          }

          if (eintrag.repeat_type == 'year') {

            let anz = new Date(eintrag_ende - eintrag_start).getFullYear() - 1970;
            for (let i = 1; i <= anz; i++) {
              let newItem = {...eintrag};
              newItem.repeat_root = [that.$dayjs(eintrag.dateStart, 'YYYY-MM-DD', true).format('YYYY-MM-DD'),
                that.$dayjs(eintrag.dateEnd, 'YYYY-MM-DD', true).format('YYYY-MM-DD')];
              let nextStart = that.$dayjs(eintrag_start).add(i, 'year');
              newItem.dateStart = nextStart.format('YYYY-MM-DD');
              newItem.dateEnd = newItem.dateStart;
              ret.push(newItem);
            }
          }

        }
      })
      data = [...data, ...ret];

      // In Monate aufteilen
      var months = {};

      data.forEach((eintrag, i) => {
        if (eintrag.dateStart) {

          let monthNameStart = that.$dayjs(eintrag.dateStart, 'YYYY-MM-DD', true).format('MM-YYYY');

          // Startmonat / Default
          if (monthNameStart && !eintrag.dateEnd) {
            if (!Array.isArray(months[monthNameStart])) {
              months[monthNameStart] = [];
            }
            months[monthNameStart].push(eintrag);
          }

          // Termin geht in den folgemonaten weiter
          if (eintrag.dateEnd) {
            let dateStart = that.$dayjs(eintrag.dateStart, 'YYYY-MM-DD', true);

            let diff = that.$dayjs(eintrag.dateEnd, 'YYYY-MM-DD', true).get("month") - dateStart.get("month");
            for(let i = 0; i <= diff; i++) {
              let monthNameNext = dateStart.add(i, 'month').format('MM-YYYY');
              if (!Array.isArray(months[monthNameNext])) {
                months[monthNameNext] = [];
              }
              months[monthNameNext].push(eintrag);
            }
          }

        }
      });

      this.loading = false;
      return months;

    },
    loadKalenders() {

      this.loading = true;
      var that = this;
      this.axios.get(this.apiURL + '/getKalenders')
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.kalenders = response.data;
                that.setPreSelected();
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Laden. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });

    },
    setPreSelected() {

      //this.selectedKalenders = [];
      this.kalenders.forEach((o) => {
        if (parseInt(o.preSelect) === 1) {
          this.selectedKalenders.push(parseInt(o.id));
        }
      });


      this.$bus.$emit('kalenders--preSelected', {
        selected: this.selectedKalenders
      });

      this.loadEvents(this.selectedKalenders);


    }

  }
}
</script>

<style>

.dp__menu {
  font-size: inherit;
}

.dp__input_icons {
  width: 1.5rem;
  height: 1.5rem;
}

.dp__input_wrap input {
  padding: 1rem 3.6rem !important;
}

.dp__action {
  padding: 1rem 1.6rem;
  background-color: #3c8dbc;
  border: 1px solid #3c8dbc;
  color: #fff;
  box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 15px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px;
  border-radius: 3rem;
  margin-right: 1.2rem;
}

.dp__action_row {
  flex-direction: column;
}

.dp__action_row .dp__selection_preview {
  width: 100%;
  padding-bottom: 2rem;
  font-size: 120%;
  letter-spacing: 0.05rem;
  text-align: center;
}

.dp__action_row .dp__action_buttons {
  width: 100%;

}

.dp__action_row .dp__action_buttons .dp__action {
  font-weight: normal;
  font-family: inherit;
}

.dp__action_row .dp__action_buttons .dp__cancel {
  box-shadow: none;
  color: #8aa4af;
  border: 1px solid #b7c7ce;
  background-color: rgba(0, 0, 0, 0);
}
</style>
