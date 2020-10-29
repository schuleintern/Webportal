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
        <div v-bind:key="l" v-for="(schueler, l) in item.schueler" class="box_odd padding-t-s padding-b-s flex-row">
          <span class="flex-3 padding-l-s" :class="{ 'text-line-through' : schueler.absenz}">
            {{schueler.rufname}} {{schueler.name}}
          </span>
          <span class="flex-1 text-right padding-r-s">
            <i v-if="schueler.geschlecht == 'm'" class="fa fa-mars" aria-hidden="true" style="color:blue"></i>
            <i v-if="schueler.geschlecht == 'w'" class="fa fa-venus" aria-hidden="true" style="color:red"></i>
            
            <span class="margin-l-s">{{schueler.klasse}}</span>
          </span>
          <div class="flex-b-100 padding-s text-right text-small">{{schueler.info}}</div>
          <div v-show="schueler.absenz" class="flex-b-100 padding-s flex-row">
            <span class="text-red flex-1 padding-l-m"><i class="fa fa-bed"></i> Absenz</span>
            <div class="flex-2 text-right">
              <div class="text-small text-grey margin-b-s"><i class="fa fa-clock"></i> {{schueler.absenz_info.stunden}}</div>
              <div class="text-small text-grey" v-html="schueler.absenz_info.notiz">{{schueler.absenz_info.notiz}}</div>
            </div>
          </div>
        </div>
      </div>

      
    </div>
  </div>

</template>


<script>

export default {
  name: 'Item',
  props: {
    dates: Array,
    acl: Object
  },
  data(){
    return {
      modalActive: false,
      deleteBtn: false,
      error: false,

      item: {
        gruppe: {},
        schueler: {}
      }

    }
  },
  created: function () {

    var that = this;

    EventBus.$on('item--open', data => {
      if (data.item) {
        that.item = data.item;
        that.modalActive = true;
      }
    });

    // EventBus.$on('item--close', data => {
    //   that.handlerCloseModal();
    // });

  },
  computed: {

  },
  methods: {

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
    },

    

  }
}
</script>
