<template>
  <div class="form-modal" v-on:click.self="handlerCloseModal" v-show="modalActive">
    <div class="form form-style-2 form-modal-content">
      <div class="form-modal-close" v-on:click="handlerCloseModal"><i class="fa fa-times"></i></div>

      <div class="text-small text-gey">Datum und Uhrzeit:</div>
      <div class="labelDay">
        {{form.startDay}}
        <span v-if="form.endDay != '0000-00-00'">bis {{form.endDay}}</span>
      </div>

      <div v-if="form.wholeDay == false" class="labelTime">
        <span v-if="form.startTime != '00:00'">{{form.startTime}}</span>
        <span v-if="form.endTime != '00:00'"> - {{form.endTime}}</span>
      </div>

      <div v-if="form.wholeDay == true">
        <div class="labelTime" v-if="form.startTime != '00:00'">{{form.startTime}}</div>
        Ganztägig
      </div>

      <br />
      <div class="text-small text-gey">Titel:</div>
      <div class="labelDay">{{form.title}}</div>
      <br />

      <div class="flex-row">
        <div class="flex-1">
          <ul class="noListStyle">
            <li v-if="form.repeat_type">
              <label class="text-small text-gey margin-r-s"><i class="fas fa-undo margin-r-s"></i>Wiederholen:</label>
              <span v-if="form.repeat_type == 'week'">Wöchentlich</span>
              <span v-if="form.repeat_type == 'month'">Monatlich</span>
              <span v-if="form.repeat_type == 'year'">Jährlich</span>
            </li>
            <li v-if="form.place">
              <label class="text-small text-gey margin-r-s"><i class="fas fa-map-marker-alt margin-r-s"></i>Ort:</label>
              {{form.place}}
            </li>
            <li v-if="form.comment" class="">
              <label class="text-small text-gey margin-r-s"><i class="fas fa-comment margin-r-s"></i>Notiz:</label>
              <br>
              <span v-html="form.comment">{{form.comment}}</span>
            </li>
            <li class="margin-t-l">
              <div class="btn noCursor" :style="{backgroundColor: formKalender.kalenderColor}">{{formKalender.kalenderName}}</div> 
            </li>
          </ul>
        </div>
      </div>

      <br />

      <button v-on:click="handlerClickEdit" v-show="acl.rights.write == 1"
      class="btn margin-r-s"><i class="fa fa-edit"></i>Bearbeiten</button>
      <button v-on:click="handlerClickDelete"
        v-show="!deleteBtn && acl.rights.delete == 1"
        class="btn"><i class="fa fa-trash"></i>Löschen</button>
      <button v-on:click="handlerClickDeleteSecond"
        v-show="deleteBtn  && acl.rights.delete == 1"
        class="btn btn-red">Endgültig Entfernen!</button>

      <div v-show="acl.rights.write == 1">
        <hr>

        <div class="text-small text-gey">
          <b>Erstellt von:</b>
          <div>{{form.createdUserName}}</div>
          <div>{{form.createdTime}} - {{form.modifiedTime}}</div>
        </div>
      </div>

    </div>
  </div>
</template>


<script>


export default {
  name: 'CalendarEintrag',
  components: {
    
  },
  props: {
    kalender: Array,
    acl: Object
  },
  data(){
    return {
      modalActive: false,
      form: {},
      formKalender: {},

      deleteBtn: false
    }
  },
  created: function () {

    var that = this;

    window.EventBus.$on('eintrag--show-open', data => {

      if (data.eintrag.title) {
        that.form = data.eintrag;
        that.formKalender = this.getKalender(data.eintrag);
      }
      that.modalActive = true;
    });

  },
  computed: {
   
  },
  methods: {
    
    handlerClickDelete: function () {
      this.deleteBtn = true;
    },
    handlerClickDeleteSecond: function () {

      //console.log(this.form);

      if (!this.form.id) {
        return false;
      }
      window.EventBus.$emit('eintrag--delete', {
        id: this.form.id
      });
      this.handlerCloseModal();
      //$event.preventDefault();
      return false;

    },

    handlerClickEdit: function () {

      if (!this.form.startDay) {
        return false;
      }
      window.EventBus.$emit('eintrag--form-open', {
        form: this.form
      });
      this.handlerCloseModal();
      //$event.preventDefault();
      return false;

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

    handlerCloseModal: function () {
      this.modalActive = false;
      this.deleteBtn = false;
    }
  }
}
</script>


<!-- Add "scoped" attribute to limit CSS to this component only -->
