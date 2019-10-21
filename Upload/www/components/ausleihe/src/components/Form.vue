<template>
  <form  id="ausleihe-form" v-on:submit.prevent class="form">
    <h2>Objekt Reservieren</h2>
    <div v-if="errorMsg" class="errorMsg">{{errorMsg}}</div>
    <ul>
      <li>
        <label>Datum</label>
        <input type="input" v-model="form.datum" required/>  
      <li>
      <li>
        <label>Klasse</label>
        <input type="input" v-model="form.klasse"  required />  
      <li>
      <li>
        <label>objektID</label>
        <input type="hidden" v-model="form.objektID"  required /> 
        <input type="input" v-model="form.objektName" readonly required /> 

      <li>
      <li>
        <label>Stunde</label>
        <input type="number" v-model="form.stunde" min="1" max="10" required />  
      <li>
        
    </ul>
    <h3>Objekte</h3>
    <ul class="objects">
      <li v-bind:key="index" v-for="(item, index) in objects"
       @click="setObjectHandler(item, $event)"
       v-bind:class="isDisable(item)">
        {{item.objektName}}
      </li>
    </ul>



    <button @click="submitHandler">Reservieren</button>
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
        'stunde': ''
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

      document.getElementById("ausleihe-form").style.display = 'block';

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

      document.getElementById("ausleihe-form").style.display = 'none';

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
            found = true;
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
      }

      if (this.selectedObject) {
        this.selectedObject.classList.remove('active');
      }
      this.selectedObject = event.target
      this.selectedObject.classList.add('active');

    },
    submitHandler: function () {

      if (!this.form.datum) { return false; }
      if (!this.form.klasse) { return false; }
      if (!this.form.objektID) { return false; }
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
