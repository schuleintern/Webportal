<template>


  <div class="form-modal " v-on:click.self="handlerCloseModal" v-show="modalActive" >
    <div class="form form-style-2 form-modal-content">
      
      <div class="form-modal-close"v-on:click="handlerCloseModal"><i class="fa fa-times"></i></div>
      
      <div v-show="error" class="form-modal-error">
        <b>Folgende Fehler sind aufgetreten:</b>
        <ul>
          <li>{{ error }}</li>
        </ul>
      </div>
    
      <h2>Reservieren</h2>

      <h4>am {{form.datum[0] | moment("Do MMMM YYYY")}} für die {{form.stunde}}. Stunde</h4>
      <h5>{{form.objektName}}</h5>

      <form id="ausleihe-form" v-on:submit.prevent class="flex-row padding-t-m">

        <input type="hidden" v-model="form.objektID"  required /> 
        <input type="hidden" class="readonly" v-model="form.objektName" readonly required /> 
        <input type="hidden" class="readonly" v-model="form.stunde" min="1" max="10" required />
        <input type="hidden" class="readonly" v-model="form.datum[1]" readonly required/>


        <div class="flex-1 margin-r-l">
          <label class="block">Objekte</label>
          <ul class="noListStyle">
            <li v-bind:key="index" v-for="(item, index) in objectsSelectable"
              class="margin-b-s" >
              <button @click="setObjectHandler(item, $event)"
                v-bind:class="isActive(item)"
                class="btn btn-grau">
                  {{item.objektName}}
              </button>
            </li>
          </ul>
        </div>

        <div class="flex-1 width-30rem">
          <ul class="noListStyle">
            <li>
              <label class="block">Klasse</label>
              <input type="text" v-model="form.klasse" maxlength="10" required />  
            </li>
            <li class="margin-t-l">
              <button @click="submitHandler"
                class="btn btn-blau width-100p">Jetzt Reservieren</button>
              <div v-if="errorMsg" class="errorMsg">{{errorMsg}}</div>
            </li>
          </ul>
        </div>

        
      </form>



      
    </div>
  </div>



      

</template>

<script>

export default {
  name: 'Form',
  props: {
    errorMsg: String,
    disableObjects: Array,
    dates: Array
  },
  data: function () {
    return {
      modalActive: false,
      error: false,

      form: {
        'datum': '',
        'klasse': '',
        'objektID': '',
        'objektName': '',
        'stunde': '',
        'sub': ''
      },
      objects: globals.objects,
      //selectedObject: false
    }
  },

  computed: {  
    objectsSelectable: function () {
      
      var booked = [];
      this.dates.forEach((o,i) => {
        if (o.ausleiheDatum == this.form.datum[1] && o.ausleiheStunde == this.form.stunde) {
          booked.push(o.ausleiheObjektID);
        }
      });
      var ret = [];
      this.objects.forEach((o,i) => {
        if ( booked.indexOf(o.objektID) == -1 ) {
          ret.push(o);
        }
      });
      return ret;

    }
  },

  created: function () {

    var that = this;
    EventBus.$on('form--open', data => {

      that.modalActive = true;

      // //document.getElementById("ausleihe-form").style.display = 'flex';
      // document.getElementById("ausleihe-form").classList.add('show');

      // // jQuery
      // $([document.documentElement, document.body]).animate({
      //     scrollTop: $("#ausleihe-form").offset().top
      // }, 600);


      for (var foo in data) {
        that.form[foo] = data[foo];
      }

      that.form['objektID'] = false;
      that.form['objektName'] = '';
      // if (that.selectedObject) {
      //   that.selectedObject.classList.remove('active');
      // }
      // that.selectedObject = false;

      // EventBus.$emit('form--check', this.form);

    });

    EventBus.$on('form--close', data => {

      //document.getElementById("ausleihe-form").style.display = 'none';
      //document.getElementById("ausleihe-form").classList.remove('show');

      this.modalActive = false;

      for (var foo in that.form) {
        that.form[foo] = '';
      }
      
      // if (that.selectedObject) {
      //   that.selectedObject.classList.remove('active');
      // }
      // that.selectedObject = false;

      document.getElementById("ausleihe-form").reset();

    });

  },
  methods: {

    isActive: function (item) {
      if ( parseInt(item.objektID) == this.form.objektID ) {
        return 'active';
      }
      return false;
    },
    setObjectHandler: function (object, event) {


      if (object.objektID) {
        this.form.objektID = object.objektID;
        this.form.objektName = object.objektName;
        this.form.sub = object.sub || false;
      }

      // if (this.selectedObject) {
      //   this.selectedObject.classList.remove('active');
      // }
      // this.selectedObject = event.target
      // this.selectedObject.classList.add('active');

      event.preventDefault();

    },
    submitHandler: function () {

      if (!this.form.datum[1]) { return false; }
      if (!this.form.klasse) { return false; }
      if (!this.form.objektID) {
        this.errorMsg = 'Bitte wählen Sie rechts ein Objekt aus.';
        return false;
      }
      if (!this.form.stunde) { return false; }

      // if (this.selectedObject) {
      //   this.selectedObject.classList.remove('active');
      // }

      this.form.datum = this.form.datum[1]; // war nur fuer frontend

      EventBus.$emit('form--submit', this.form);

    },

    handlerCloseModal: function () {
      this.modalActive = false;
    },

  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
