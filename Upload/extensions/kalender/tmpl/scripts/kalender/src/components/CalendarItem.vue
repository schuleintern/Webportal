<template>

  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box">
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">



        <div class="text-small text-gey">Datum und Uhrzeit:</div>
        <div class="labelDay">
          {{item.startDay}}
          <span v-if="item.endDay != '0000-00-00'">bis {{item.endDay}}</span>
        </div>

        <div v-if="item.wholeDay == false" class="labelTime">
          <span v-if="item.startTime != '00:00'">{{item.startTime}}</span>
          <span v-if="item.endTime != '00:00'"> - {{item.endTime}}</span>
        </div>

        <div v-if="item.wholeDay == true">
          <div class="labelTime" v-if="item.startTime != '00:00'">{{item.startTime}}</div>
          Ganztägig
        </div>

        <br />
        <div class="text-small text-gey">Titel:</div>
        <div class="labelDay">{{item.title}}</div>
        <br />

        <div class="flex-row">
          <div class="flex-1">
            <ul class="noListStyle">
              <li v-if="item.repeat_type">
                <label class="text-small text-gey margin-r-s"><i class="fas fa-undo margin-r-s"></i>Wiederholen:</label>
                <span v-if="item.repeat_type == 'week'">Wöchentlich</span>
                <span v-if="item.repeat_type == 'month'">Monatlich</span>
                <span v-if="item.repeat_type == 'year'">Jährlich</span>
              </li>
              <li v-if="item.place">
                <label class="text-small text-gey margin-r-s"><i class="fas fa-map-marker-alt margin-r-s"></i>Ort:</label>
                {{item.place}}
              </li>
              <li v-if="item.comment" class="">
                <label class="text-small text-gey margin-r-s"><i class="fas fa-comment margin-r-s"></i>Notiz:</label>
                <br>
                <span v-html="item.comment"></span>
              </li>
              <li class="margin-t-l">
                <div class="btn noCursor" :style="{backgroundColor: formKalender.kalenderColor}">{{formKalender.kalenderName}}</div>
              </li>
            </ul>
          </div>
        </div>

        <br />

        <button v-on:click="handlerClickEdit" v-show="acl.write == 1"
                class="si-btn margin-r-m"><i class="fa fa-edit"></i>Bearbeiten</button>
        <button v-on:click="handlerClickDelete"
                v-show="!deleteBtn && acl.delete == 1"
                class="si-btn"><i class="fa fa-trash"></i>Löschen</button>
        <button v-on:click="handlerClickDeleteSecond"
                v-show="deleteBtn && acl.delete == 1"
                class="si-btn si-btn-red">Endgültig Entfernen!</button>

        <div v-show="acl.write == 1" class="flex-row text-small text-gey">
          <hr class="flex-b-100">

          <div class="margin-r-l" v-if="item.createdTime">
            <b> Erstellt von:</b>
            <div>{{item.createdUserName}}</div>
            <div>{{item.createdTime}} id: {{item.id}}</div>
          </div>
          <div class="" v-if="item.modifiedTime">
            <b>Bearbeitet am:</b>
            <div>{{item.modifiedTime}}</div>
          </div>
        </div>




        
      </div>
    </div>
  </div>


</template>

<script>

export default {
  name: 'CalendarItem',
  setup() {

  },
  data() {
    return {
      open: false,
      item: false,

      deleteBtn: false,
      formKalender: {}
    };
  },
  props: {
    acl: Object,
    kalender: Array
  },
  created: function () {

    var that = this;
    this.$bus.$on('event-item--open', data => {
      if (data.item) {
        that.item = data.item;
        that.formKalender = this.getKalender(data.eintrag);
      }

      that.open = true;
    });
    this.$bus.$on('event-item--close', data => {
      that.item = false;
      that.open = false;
    });


  },
  methods: {

    handlerClose: function () {
      this.$bus.$emit('event-item--close');
    },

    getKalender: function (eintrag) {
      if (!eintrag || !eintrag.calenderID) {
        return false;
      }
      var calenderID = parseInt(eintrag.calenderID);
      if (!calenderID) {
        return false;
      }
      var ret = false;
      this.kalender.forEach(function (o) {
        if ( parseInt(o.kalenderID) == calenderID ) {
          ret = o;
        }
      });
      return ret;
    },


    handlerClickDelete: function () {
      this.deleteBtn = true;
    },
    handlerClickDeleteSecond: function () {

      if (!this.item.id) {
        return false;
      }
      this.$bus.$emit('event-item--delete', {
        id: this.item.id
      });
      this.handlerClose();
      //$event.preventDefault();
      return false;

    },

    handlerClickEdit: function () {

      if (!this.item.startDay) {
        return false;
      }
      this.$bus.$emit('event-form--open', {
        form: this.item
      });
      this.handlerClose();
      //$event.preventDefault();
      return false;

    },


  }


};
</script>

<style>

</style>