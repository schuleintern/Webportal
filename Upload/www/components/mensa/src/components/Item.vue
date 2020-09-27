<template>

  <div class="form-modal" v-on:click.self="handlerCloseModal" v-show="modalActive" >
    <div class="form form-style-2 form-modal-content width-40vw">
      
      <div class="form-modal-close" v-on:click="handlerCloseModal"><i class="fa fa-times"></i></div>
      
      <br />

      <div class="text-big-s text-grey margin-b-s">{{ $date(item.date).format("dddd DD.MM.YYYY") }}</div>

      <div class="text-big-m margin-b-l">{{ item.title }}</div>

      <div class="margin-b-l text-green flex-row-wrap">
        <div v-if="item.vegetarisch == 1" class="flex-b-50"><i class="fas fa-seedling width-2rem"></i> Vegetarisch</div>
        <div v-if="item.vegan == 1" class="flex-b-50"><i class="fas fa-leaf width-2rem"></i> Vegan</div>
        <div v-if="item.laktosefrei == 1" class="flex-b-50"><i class="fas fa-wine-bottle width-2rem "></i> Laktosefrei</div>
        <div v-if="item.glutenfrei == 1" class="flex-b-50"><i class="fab fa-pagelines width-2rem"></i> Glutenfrei</div>
        <div v-if="item.bio == 1" class="flex-b-50"><i class="fas fa-leaf width-2rem"></i> Bio</div>
        <div v-if="item.regional == 1" class="flex-b-50"><i class="fas fa-tractor width-2rem"></i> Regional</div>
      </div>

      <div class="margin-b-m">
        <div class="" v-show="item.preis_default"><label>Bedienstete:</label> {{ replaceFloat(item.preis_default) }} €</div>
        <div class="" v-show="item.preis_schueler"><label>Schüler:</label> {{ replaceFloat(item.preis_schueler) }} €</div>
      </div>

      <div class="" v-html="item.desc">{{ item.desc }}</div>


      <div class="margin-t-l padding-b-l">

        <hr>
        
        <div v-if="item.booked_all">
          <h3 class="text-orange">Gebuchte Essen</h3>

          <div class="flex-row">
            <div class="flex-1 margin-r-l">
              <div class="margin-b-m">
                <label>Summe:</label> {{item.booked_all.summe}}
              </div>
            </div>
            <div class="flex-1">
              <div>
                <label>Schüler:</label>
                {{item.booked_all.schueler}}
              </div>
              <div>
                <label>Eltern:</label>
                {{item.booked_all.eltern}}
              </div>
              <div>
                <label>Lehrer:</label>
                {{item.booked_all.lehrer}}
              </div>
              <div>
                <label>Mitarbeiter:</label>
                {{item.booked_all.none}}
              </div>
            </div>
          </div>

          <br/>

          <h4>Benutzerliste</h4>
          <ul class="noListStyle">
            <li v-bind:key="j" v-for="(user, j) in item.booked_all.list"
              class="flex-row">
              <div class="flex-2">{{user[1]}}</div>
              <div class="flex-1 text-small">{{user[2]}}</div>
              <div class="flex-1 text-small">{{user[3]}}</div>
            </li>
          </ul>
        </div>

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
