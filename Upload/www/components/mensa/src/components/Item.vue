<template>

  <div class="form-modal" v-on:click.self="handlerCloseModal" v-show="modalActive" >
      <div class="form form-style-2 form-modal-content">
        
        <div class="form-modal-close"v-on:click="handlerCloseModal"><i class="fa fa-times"></i></div>
        
        <br />

        <div class="text-big-s text-grey margin-b-s">{{ $date(item.date).format("dddd DD.MM.YYYY") }}</div>

        <div class="text-big-m margin-b-l">{{ item.title }}</div>

        <div class="margin-b-m text-green">
          <div v-if="item.vegetarisch == 1"><i class="fas fa-seedling"></i> Vegetarisch</div>
          <div v-if="item.vegan == 1"><i class="fas fa-leaf"></i> Vegan</div>
          <div v-if="item.laktosefrei == 1"><i class="fas fa-wine-bottle"></i> Laktosefrei</div>
          <div v-if="item.glutenfrei == 1"><i class="fab fa-pagelines"></i> Glutenfrei</div>
          <div v-if="item.bio == 1"><i class="fas fa-leaf"></i> Bio</div>
          <div v-if="item.regional == 1"><i class="fas fa-tractor"></i> Regional</div>
        </div>

        <div class="margin-b-m">
          <div class="" v-show="item.preis_default"><label>Bedienstete:</label> {{ replaceFloat(item.preis_default) }} €</div>
          <div class="" v-show="item.preis_schueler"><label>Schüler:</label> {{ replaceFloat(item.preis_schueler) }} €</div>
        </div>

        <div class="" v-html="item.desc">{{ item.desc }}</div>

        <br/>

        <div>
          <hr>
          <button @click="openForm(item)" class="btn margin-r-s"><i class="fa fa-edit"></i> Bearbeiten</button>
          
          <button v-on:click="handlerClickDelete"
            v-show="!deleteBtn"
            class="btn"><i class="fa fa-trash"></i>Löschen</button>
          <button v-on:click="handlerClickDeleteSecond(item)"
            v-show="deleteBtn"
            class="btn btn-red">Endgültig Entfernen!</button>
        </div>
      </div>
    </div>

</template>


<script>

export default {
  name: 'Item',
  props: {
    dates: Array
  },
  data(){
    return {
      modalActive: false,
      deleteBtn: false,
      error: false,

      item: {}

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
