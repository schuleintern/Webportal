<template>
  <div id="app">

    <div v-if="loading == true" class="overlay">
      <i class="fa fas fa-sync-alt fa-spin"></i>
    </div>

    <div v-if="error" class="form-modal-error">
      <b>Folgende Fehler sind aufgetreten:</b>
      <ul>
        <li>{{ error }}</li>
      </ul>
    </div>

    <CalendarForm
        v-bind:kalender="kalender"
        v-bind:calendarSelected="calendarSelected"
        v-bind:acl="acl"></CalendarForm>

    <CalendarEintrag v-bind:kalender="kalender"
                     v-bind:acl="acl"></CalendarEintrag>


    <div id="" class="">
      <CalendarList v-bind:kalender="kalender"></CalendarList>
      <Calendar
          v-bind:content="month"
          v-bind:kalender="kalender"
          v-bind:acl="acl"></Calendar>
    </div>

  </div>
</template>

<script>
//console.log('globals',globals);

import Calendar from './components/Calendar.vue'
import CalendarList from './components/CalendarList.vue'
import CalendarForm from './components/CalendarForm.vue'
import CalendarEintrag from './components/CalendarEintrag.vue'

const axios = require('axios').default;

//axios.defaults.headers.common['schuleinternapirequest'] = '112233' // for all requests

export default {
  name: 'app',
  components: {
    Calendar,
    CalendarList,
    CalendarForm,
    CalendarEintrag
  },
  data: function () {
    return {

      loading: true,
      error: false,

      calendarSelected: [],

      kalender: [],
      month: [],

      acl: false

    }
  },
  created: function () {

    this.acl = globals.acl;

    //console.log(globals);
    var that = this;

    that.ajaxGet(
        'rest.php/GetKalender',
        {},
        function (response, that) {

          if (response.data.error == true && response.data.msg) {
            that.error = response.data.msg;
          } else {

            if (response.data.list && that.acl.rights.read) {
              //that.calendarSelected = [ parseInt(response.data.list[0].kalenderID) ];

              that.kalender = response.data.list;
              that.kalender.forEach(function (o, i) {
                if (o.kalenderPreSelect == 1) {
                  that.calendarSelected.push(parseInt(o.kalenderID));
                }
              });


              window.EventBus.$emit('list--preselected', {
                selected: that.calendarSelected
              });

              window.EventBus.$emit('eintrag--load', {});

            }

          }


        }
    );

    window.EventBus.$on('list--selected', data => {

      that.calendarSelected = data.selected;
      window.EventBus.$emit('eintrag--load', {});
    });


    window.EventBus.$on('eintrag--load', data => {

      that.ajaxGet(
          'rest.php/GetKalenderEintrag/' + that.calendarSelected.join('-') + '/short',
          {},
          function (response, that) {

            //console.log(response.data);
            if (response.data.error == true && response.data.msg) {
              that.error = response.data.msg;
            } else {
              if (response.data && response.data.list && that.acl.rights.read) {
                that.setEintraege(response.data.list);
              } else {
                that.month = [];
              }
              that.error = '';
            }
          }
      );
    });


    window.EventBus.$on('eintrag--delete', data => {

      if (that.acl.rights.delete != 1) {
        that.error = "Keine Löschrechte!";
        return false;
      }

      if (!data.id) {
        return false;
      }
      that.ajaxPost(
          'rest.php/DeleteKalenderEintrag',
          {data: data.id},
          {},
          function (response, that) {

            //console.log(response.data);

            if (response.data.error == true && response.data.msg) {
              that.error = response.data.msg;
            } else if (response.data.done == true) {

              window.EventBus.$emit('eintrag--load', {});

            }

          }
      );
    });


    window.EventBus.$on('eintrag--submit', data => {


      if (that.acl.rights.write != 1) {
        that.error = "Keine Schreibrechte!";
        return false;
      }

      if (data.form.start == ''
          && data.form.title == ''
          && data.form.calenderID == '') {
        return false;
      }

      that.ajaxPost(
          'rest.php/SetKalenderEintrag',
          {data: data.form},
          {},
          function (response) {

            if (response.data.error == true && response.data.msg) {
              that.error = response.data.msg;
            } else if (response.data.done == true) {
              window.EventBus.$emit('eintrag--form-reset', {});
              window.EventBus.$emit('eintrag--load', {});
            }

          }
      );

    });


  },
  methods: {

    setEintraege: function (data) {

      this.loading = true;
      var ret = [];

      var that = this;

      data.forEach((eintrag) => {


        /*
          REPEAT
        */

        if (eintrag.eintragRepeat) {

          var eintrag_start = new Date(eintrag.eintragDatumStart);
          var eintrag_ende = new Date(eintrag.eintragDatumEnde);

          if (eintrag.eintragRepeat == 'week') {
            const ms = 1000 * 60 * 60 * 24 * 7;
            let anz = Math.round(Math.abs(eintrag_ende - eintrag_start) / ms);
            for (var i = 1; i <= anz; i++) {
              let newItem = {...eintrag};
              newItem.repeat_root = [that.$moment(eintrag.eintragDatumStart, 'YYYY-MM-DD', true).format('YYYY-MM-DD'),
                that.$moment(eintrag.eintragDatumEnde, 'YYYY-MM-DD', true).format('YYYY-MM-DD')];
              let nextStart = that.$moment(eintrag_start).add(i, 'week');
              newItem.eintragDatumStart = nextStart.format('YYYY-MM-DD');
              newItem.eintragDatumEnde = newItem.eintragDatumStart;
              ret.push(newItem);
            }
          }

          if (eintrag.eintragRepeat == 'month') {
            let anz = (
                eintrag_ende.getMonth() -
                eintrag_start.getMonth() +
                12 * (eintrag_ende.getFullYear() - eintrag_start.getFullYear())
            )
            for (let i = 1; i <= anz; i++) {
              let newItem = {...eintrag};
              newItem.repeat_root = [that.$moment(eintrag.eintragDatumStart, 'YYYY-MM-DD', true).format('YYYY-MM-DD'),
                that.$moment(eintrag.eintragDatumEnde, 'YYYY-MM-DD', true).format('YYYY-MM-DD')];
              let nextStart = that.$moment(eintrag_start).add(i, 'month');
              newItem.eintragDatumStart = nextStart.format('YYYY-MM-DD');
              newItem.eintragDatumEnde = newItem.eintragDatumStart;
              ret.push(newItem);
            }
          }

          if (eintrag.eintragRepeat == 'year') {
            let anz = new Date(eintrag_ende - eintrag_start).getFullYear() - 1970;
            for (let i = 1; i <= anz; i++) {
              let newItem = {...eintrag};
              newItem.repeat_root = [that.$moment(eintrag.eintragDatumStart, 'YYYY-MM-DD', true).format('YYYY-MM-DD'),
                that.$moment(eintrag.eintragDatumEnde, 'YYYY-MM-DD', true).format('YYYY-MM-DD')];
              let nextStart = that.$moment(eintrag_start).add(i, 'year');
              newItem.eintragDatumStart = nextStart.format('YYYY-MM-DD');
              newItem.eintragDatumEnde = newItem.eintragDatumStart;
              ret.push(newItem);
            }
          }


        }


      })

      data = [...data, ...ret];

      // In Monate aufteilen
      this.month = [];
      data.forEach((eintrag) => {

        if (parseInt(eintrag.eintragDatumStart) > 0) {

          eintrag.eintragTimeStart = that.$moment(eintrag.eintragTimeStart, 'HH:mm:ss', true).format('HH:mm');
          eintrag.eintragTimeEnde = that.$moment(eintrag.eintragTimeEnde, 'HH:mm:ss', true).format('HH:mm');

          let monthName = that.$moment(eintrag.eintragDatumStart, 'YYYY-MM-DD', true).format('MM-YYYY');
          if (!Array.isArray(this.month[monthName])) {
            this.month[monthName] = [];
          }
          this.month[monthName].push(eintrag);
        }
      });

      this.loading = false;
      //console.log(this.month);


    },

    ajaxGet: function (url, params, callback, error, allways) {
      this.loading = true;
      var that = this;
      axios.get(url, {
        params: params
      })
          .then(function (response) {
            // console.log(response.data);
            if (callback && typeof callback === 'function') {
              callback(response, that);
            }
          })
          .catch(function (resError) {
            //console.log(error);
            if (resError && typeof error === 'function') {
              error(resError);
            }
          })
          .finally(function () {
            // always executed
            if (allways && typeof allways === 'function') {
              allways();
            }
            that.loading = false;
          });

    },
    ajaxPost: function (url, data, params, callback, error, allways) {
      this.loading = true;
      var that = this;
      axios.post(url, data, {
        params: params
      })
          .then(function (response) {
            // console.log(response.data);
            if (callback && typeof callback === 'function') {
              callback(response, that);
            }
          })
          .catch(function (resError) {
            //console.log(error);
            if (resError && typeof error === 'function') {
              error(resError);
            }
          })
          .finally(function () {
            // always executed
            if (allways && typeof allways === 'function') {
              allways();
            }
            that.loading = false;
          });

    }


  }
}
</script>

<style>
</style>
