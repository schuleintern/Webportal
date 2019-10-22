<template>
  <form id="ausleihe-form" v-on:submit.prevent 
    class="form box box-solid box-warning">

    
    <div class="flexbox">
      <h2>Reservieren</h2>
      

      <input type="hidden" v-model="form.objektID"  required /> 
      <input type="hidden" class="readonly" v-model="form.objektName" readonly required /> 
      <input type="hidden" class="readonly" v-model="form.stunde" min="1" max="10" required />
      <input type="hidden" class="readonly" v-model="form.datum" readonly required/>


      <ul>
        <li>
          <h4>am {{form.datum}} für die {{form.stunde}}. Stunde</h4>
          <h5>{{form.objektName}}</h5>
        </li>
        <li>
          <label>Klasse</label>
          <input type="input" v-model="form.klasse" maxlength="10" required />  
        </li>
        <li>
          <button @click="submitHandler"
            class="btn btn-primary">Jetzt Reservieren</button>
          <div v-if="errorMsg" class="errorMsg">{{errorMsg}}</div>
        </li>
      </ul>
    </div>

    <div class="flexbox">
      <h3>Objekte</h3>
      <div class="objects">
        <button v-bind:key="index" v-for="(item, index) in objects"
        @click="setObjectHandler(item, $event)"
        v-bind:class="isDisable(item)"
        class="btn btn-info">
          {{item.objektName}}
        </button>
      </div>
    </div>
    
  </form>
</template>

<script>

export default {
  name: 'Form',
  props: {
    errorMsg: String,
    disableObjects: Array
  },
  data: function () {
    return {
      form: {
        'datum': '',
        'klasse': '',
        'objektID': '',
        'objektName': '',
        'stunde': '',
        'sub': ''
      },
      objects: globals.objects,
      selectedObject: false
    }
  },

  computed: {  
  },

  created: function () {

    var that = this;
    EventBus.$on('form--open', data => {

      //document.getElementById("ausleihe-form").style.display = 'flex';
      document.getElementById("ausleihe-form").classList.add('show');

      // jQuery
      $([document.documentElement, document.body]).animate({
          scrollTop: $("#ausleihe-form").offset().top
      }, 600);


      for (var foo in data) {
        that.form[foo] = data[foo];
      }

      that.form['objektID'] = false;
      that.form['objektName'] = '';
      if (that.selectedObject) {
        that.selectedObject.classList.remove('active');
      }
      that.selectedObject = false;

      EventBus.$emit('form--check', this.form);

    });

    EventBus.$on('form--close', data => {

      //document.getElementById("ausleihe-form").style.display = 'none';
      document.getElementById("ausleihe-form").classList.remove('show');

      for (var foo in that.form) {
        that.form[foo] = '';
      }
      
      if (that.selectedObject) {
        that.selectedObject.classList.remove('active');
      }
      that.selectedObject = false;

      document.getElementById("ausleihe-form").reset();

    });

  },
  methods: {

    isDisable: function (item) {

      if (this.disableObjects) {
        var found = false;
        this.disableObjects.forEach( function (value, index, array) {
          if ( parseInt(item.objektID) == parseInt(value.ausleiheObjektID)) {
            if (value.sub > 0 && item.sub> 0) {
              if ( value.sub == item.sub ) {
                found = true;
              }
            }  else {
              found = true;
            }
          }
        });
        if (found) {
          return 'disable';
        }
      }
      return false;
      
    },
    setObjectHandler: function (object, event) {

      if ( event.target.classList.contains('disable')) {
        return false;
      }
      if (object.objektID) {
        this.form.objektID = object.objektID;
        this.form.objektName = object.objektName;
        this.form.sub = object.sub || false;
      }

      if (this.selectedObject) {
        this.selectedObject.classList.remove('active');
      }
      this.selectedObject = event.target
      this.selectedObject.classList.add('active');

      event.preventDefault();

    },
    submitHandler: function () {

      if (!this.form.datum) { return false; }
      if (!this.form.klasse) { return false; }
      if (!this.form.objektID) {
        this.errorMsg = 'Bitte wählen Sie rechts ein Objekt aus.';
        return false;
      }
      if (!this.form.stunde) { return false; }

      if (this.selectedObject) {
        this.selectedObject.classList.remove('active');
      }

      EventBus.$emit('form--submit', this.form);

    }

  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
