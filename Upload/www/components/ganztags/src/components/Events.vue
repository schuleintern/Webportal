<template>

  <div class="form-modal" v-on:click.self="handlerCloseModal" v-show="modalActive" >
    <div class="form form-style-2 form-modal-content width-40vw">
      
      <div class="form-modal-close" v-on:click="handlerCloseModal"><i class="fa fa-times"></i></div>
      
      <br />

      <div class="text-big-2">{{item.gruppe.name}}</div>
      <div class="padding-t-s padding-b-s">
        <span v-show="item.schueler.length" class="text-white bg-grau border-radius padding-t-xs padding-b-xs padding-l-s padding-r-s margin-r-m text-bold"
              v-bind:style="{ backgroundColor: item.gruppe.farbe }">
          <i class="fa fa-child margin-r-m"></i>{{item.schueler.length}}</span>
        <span v-show="item.gruppe.absenz_anz" class="bg-red text-white border-radius padding-t-xs padding-b-xs padding-l-s padding-r-s margin-r-m text-bold">
          <i class="fa fa-bed margin-r-m"></i>{{item.gruppe.absenz_anz}}</span>
        <span class="flex-1" v-show="item.gruppe.raum"><i class="fas fa-map-marker-alt"></i> {{item.gruppe.raum}}</span>
      </div>

      <div class="margin-t-m">
        <div v-bind:key="a" v-for="(event, a) in item.events"
             v-on:click="handlerEdit(event)"
             class="padding-s line-oddEven" >
          <div class="">{{event.title}}</div>
          <div class="text-small text-right">{{event.room}}</div>
        </div>
      </div>

      <div class="margin-t-m border-t padding-m bg-grau-hell">
        <div class="padding-s">
          <label>Titel</label>
          <input type="text" class="width-100p" v-model="form.title" >
        </div>
        <div class="padding-s">
          <label class="width-7rem">Raum</label>
          <input type="text" class="width-100p" v-model="form.room" >
        </div>
        <button v-show="!form.id" v-on:click="handlerSubmit()" class="btn width-100p margin-t-m"><i class="fa fa-save"></i>Hinzufügen</button>
        <button v-show="form.id" v-on:click="handlerSubmit()" class="btn width-100p margin-t-m"><i class="fa fa-save"></i>Speichern</button>
      </div>
      
    </div>
  </div>

</template>


<script>

export default {
  name: 'Events',
  props: {
    acl: Object
  },
  data(){
    return {
      modalActive: false,
      deleteBtn: false,
      error: false,

      item: {
        gruppe: {},
        schueler: {},
        events: {}
      },

      form: {
        id: false,
        title: '',
        room: '',
        day: false,
        gruppeID: false
      }

    }
  },
  created: function () {

    var that = this;

    EventBus.$on('events--open', data => {
      if (data.item) {
        that.item = data.item;
        that.form.day = data.day;
        that.form.gruppeID = data.item.gruppe.id;

        that.modalActive = true;
      }
    });



    EventBus.$on('events--close', data => {
      that.handlerCloseModal();
    });

  },
  computed: {

  },
  methods: {

    handlerEdit: function(event) {

      //console.log(event);

      if (event.id) {
        this.form.id = event.id;
        this.form.title = event.title;
        this.form.room = event.room;
      }


    },
    handlerSubmit: function() {

      EventBus.$emit('events--insert', {
        item: this.form
      });

    },
    handlerClickDelete: function () {
      this.deleteBtn = true;
    },
    handlerClickDeleteSecond: function (item) {
      // console.log(item);

      if (!item.id) {
        return false;
      }
      EventBus.$emit('item--delete', {
        item: item
      });
      this.handlerCloseModal();
      return false;
    },

    openForm: function (item) {

      if (!item) {
        return false;
      }
      EventBus.$emit('form--open', {
        item: item
      });
      this.handlerCloseModal();
      return false;

    },

    replaceFloat: function (str) {
      if (!str) {
        return false;
      }
      return Number.parseFloat(str).toFixed(2).replace('.', ',');
    },
    handlerCloseModal: function () {
      this.modalActive = false;
      this.deleteBtn = false;
      this.form = {
        id: false,
        title: '',
        room: '',
        day: false,
        gruppeID: false
      };

    },

    

  }
}
</script>
