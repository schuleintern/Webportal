<template>
  <div class="form-modal" v-on:click.self="handlerCloseModal" v-show="modalActive">
    <div class="form form-style-2 form-modal-content">
      <div class="form-modal-close"v-on:click="handlerCloseModal"><i class="fa fa-times"></i></div>


      <h4>{{form.day}}</h4>

      <div v-if="form.wholeDay == false">
        <h3 v-if="form.startTime">{{form.startTime}}</h3>
        <h3 v-if="form.endTime"> - {{form.endTime}}</h3>
      </div>

      <div v-if="form.wholeDay == true">
        <h3 v-if="form.startTime != '00:00'">{{form.startTime}}</h3>
        Ganztägig
      </div>

      <h3>{{form.title}}</h3>
      

      <div class="flex-row">
        <div class="flex-1">
          <ul class="noListStyle">
            <li>
              <label>Place:</label>
              {{form.place}}
            </li>
            <li>
              <label>Comment:</label>
              {{form.comment}}
            </li>
            <li>
              <div class="btn" :style="{backgroundColor: formKalender.kalenderColor}">{{formKalender.kalenderName}}</div> 
            </li>
          </ul>
        </div>
      </div>

      <hr>

      <div>
        <h6>Erstellt von:</h6>
        {{form.createdUserName}} - {{form.createdTime}} - {{form.modifiedTime}}
      </div>

      <button v-on:click="handlerClickEdit">Bearbeiten</button>
      <button v-on:click="handlerClickDelete"
        v-show="!deleteBtn">Löschen</button>
      <button v-on:click="handlerClickDeleteSecond"
        v-show="deleteBtn">Wirklich Löschen</button>

    </div>
  </div>
</template>


<script>


export default {
  name: 'CalendarEintrag',
  components: {
    
  },
  props: {
    kalender: Array
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

    EventBus.$on('eintrag--show-open', data => {

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

      console.log(this.form);

      if (!this.form.id) {
        return false;
      }
      EventBus.$emit('eintrag--delete', {
        id: this.form.id
      });
      this.handlerCloseModal();
      //$event.preventDefault();
      return false;

    },

    handlerClickEdit: function () {

      if (!this.form.day) {
        return false;
      }
      EventBus.$emit('eintrag--form-open', {
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
      this.kalender.forEach(function (o,i) {
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
