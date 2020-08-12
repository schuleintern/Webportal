<template>
  <div class="flex-1 bg-white form-recipients flex-row height_35 border-radius">
    
    <div class="flex-1 flex border-r scroll">
      <button @click="clickHandlerRecipientsTabOpen('tab-1')" v-bind:class="isTabActive('tab-1')" class="btn chevron">Allgemein</button>
      <button @click="clickHandlerRecipientsTabOpen('tab-2')" v-bind:class="isTabActive('tab-2')" class="btn chevron">Lehrer</button>
      <button @click="clickHandlerRecipientsTabOpen('tab-3')" v-bind:class="isTabActive('tab-3')" class="btn chevron">Schüler</button>
      <button @click="clickHandlerRecipientsTabOpen('tab-4')" v-bind:class="isTabActive('tab-4')" class="btn chevron">Eltern</button>
      <button @click="clickHandlerRecipientsTabOpen('tab-5')" v-bind:class="isTabActive('tab-5')" class="btn chevron">Sonstige</button>
    </div>
    <div class="flex-1 border-r">
      <div class="tab flex scroll" v-show="recipientsTabOpen == 'tab-1'">
        <button class="btn" @click="clickHandlerRecipientsSelect({key: 'SL', name:'Schulleitung'})" v-bind:class="isRecipientSelected({key: 'SL'})" >Schulleitung</button>
        <button class="btn" @click="clickHandlerRecipientsSelect({key: 'PR', name:'Personalrat'})" v-bind:class="isRecipientSelected({key: 'PR'})" >Personalrat</button>
        <button class="btn" @click="clickHandlerRecipientsSelect({key: 'VW', name:'Verwaltung'})" v-bind:class="isRecipientSelected({key: 'VW'})" >Verwaltung</button>
        <button class="btn" @click="clickHandlerRecipientsSelect({key: 'HM', name:'Hausmeister'})" v-bind:class="isRecipientSelected({key: 'HM'})" >Hausmeister</button>
      </div>
      <div class="tab flex scroll" v-show="recipientsTabOpen == 'tab-2'">
        <button class="btn" @click="clickHandlerRecipientsSelect({key: 'all_teacher', name:'Alle Lehrer'})" v-bind:class="isRecipientSelected({key: 'all_teacher'})" >Alle Lehrer</button>
        <button @click="clickHandlerRecipientsLehrer('list-1')" v-bind:class="isListActive('list-1')" class="btn chevron">Alle</button>
        <button @click="clickHandlerRecipientsFachschaft('list-2')" v-bind:class="isListActive('list-2')" class="btn chevron">Fachschaft</button>
        <button @click="clickHandlerKlassenteam('list-3')" v-bind:class="isListActive('list-3')" class="btn chevron">Klassenlehrer</button>
        <button @click="clickHandlerKlassenleitung('list-4')" v-bind:class="isListActive('list-4')" class="btn chevron">Klassenleitungen</button>
      </div>
      <div class="tab flex scroll" v-show="recipientsTabOpen == 'tab-3'">
        <button @click="clickHandlerRecipientsSchueler('list-1')" v-bind:class="isListActive('list-1')" class="btn chevron">Alle</button>
        <button @click="clickHandlerRecipientsSchuelerKlassen('list-2')" v-bind:class="isListActive('list-2')" class="btn chevron">Klassen</button>
        <button @click="clickHandlerRecipientsSchuelerOwnUnterricht('list-3')" v-bind:class="isListActive('list-3')" class="btn chevron">Des eigenen Unterrichts</button>
        <button @click="clickHandlerRecipientsSchuelerAllUnterricht('list-4')" v-bind:class="isListActive('list-4')" class="btn chevron">Aller Unterrichte</button>
      </div>
      <div class="tab flex scroll" v-show="recipientsTabOpen == 'tab-4'">
        <button @click="clickHandlerRecipientsElternKlassen('list-1')" v-bind:class="isListActive('list-1')" class="btn chevron">Eltern der ganzen Klasse</button>
        <button @click="clickHandlerRecipientsElternSingel('list-2')" v-bind:class="isListActive('list-2')" class="btn chevron">Einzelne Eltern</button>
        <button @click="clickHandlerRecipientsOwnUnterricht('list-3')" v-bind:class="isListActive('list-3')" class="btn chevron">Eltern der Schüler des eigenen Unterrichts</button>
        <button @click="clickHandlerRecipientsAllUnterricht('list-4')" v-bind:class="isListActive('list-4')" class="btn chevron">Eltern der Schüler aller Unterrichte</button>
      </div>
      <div class="tab flex scroll" v-show="recipientsTabOpen == 'tab-5'">
        gruppe,,,,
      </div>
    </div>
    <div class="flex-1 flex border-r margin-r-xs height_35 " >
      <div v-show="recipientsResultSearched">
        <input type="search" v-model="searchQuery" placeholder="Suche..." />
        <button @click="clickHandlerSearchClear()">Clear</button>
      </div>

      <div class="scroll">
        <button v-bind:key="index" v-for="(item, index) in recipientsResultSearched"
          @click="clickHandlerRecipientsSelect(item)"
          v-bind:class="isRecipientSelected(item)" class="btn">{{item.text}}</button>
      </div>
    </div>
    <div class="flex-1 flex border-l scroll">

      <button v-bind:key="index" v-for="(item, index) in recipientsSelect"
        @click="clickHandlerRecipientsSelect(item)"
        v-bind:class="isRecipientSelected(item)" class="btn">{{item.name}}</button>

      <button @click="clickCloseForm()" class="btn btn-blau">OK</button>

    </div>
  </div>
  
</template>

<script>

//import GridTemplate from './GridTemplate.vue'

const axios = require('axios').default;

export default {
  name: 'FormRecipient',
  components: {
    //GridTemplate
  },
  props: {
    //messages: Array
    //recipientsSelectString: String,
    type: String
  },
  data: function () {
    return {

      recipientsTabOpen: 'tab-1',

      //recipientsSelectString: '',
      recipientsSelect: [],
      recipientsResult: [],

      recipientsListOpen: '',

      searchQuery: ''
    }
  },
  computed: {


    recipientsResultSearched: function () {
 
      if (this.searchQuery && this.recipientsResult) {

        let list = this.recipientsResult;
        let query = this.searchQuery;

        list = list.filter(function (row) {
          return Object.keys(row).some(function (key) {
            return String(row[key]).toLowerCase().indexOf(query) > -1
          })
        })
        return list;
      }
      
      return this.recipientsResult;
    }

  },
  
  created: function () {

  },
  methods: {

    clickHandlerSearchClear: function () {

      this.searchQuery = '';

    },
    clickHandlerRecipientsTabOpen: function (tab) {

      this.recipientsTabOpen = tab;
      this.recipientsListOpen = false;

      this.recipientsResult = false;

    },
    clickHandlerRecipientsLehrer: function (list) {

      var that = this;

      axios.get('index.php?page=MessageCompose&action=getTeachersJSON&_type=query', {
        params: {}
      })
      .then(function (response) {
        // console.log(response.data);
        if (response.data.results) {
          that.recipientsResult = response.data.results;

          that.recipientsListOpen = list;
        }
      })
      .catch(function (resError) {
        console.log(resError);
      })

    },
    clickHandlerRecipientsFachschaft: function (list) {

      this.recipientsResult = globals.selectOptionsFachschaften;
      this.recipientsListOpen = list;
    },
    clickHandlerKlassenteam: function (list) {

      this.recipientsResult = globals.selectOptionsKlassenteams;
      this.recipientsListOpen = list;
    },
    clickHandlerKlassenleitung: function (list) {

      this.recipientsResult = globals.selectOptionsKlassenleitung;
      this.recipientsListOpen = list;
    },
    clickHandlerRecipientsSchueler:  function (list) {
      this.recipientsResult = globals.selectOptionsSchueler;
      this.recipientsListOpen = list;
    },
    clickHandlerRecipientsSchuelerOwnUnterricht:  function (list) {
      this.recipientsResult = globals.selectOptionsSchuelerOwnUnterricht;
      this.recipientsListOpen = list;
    },

    clickHandlerRecipientsSchuelerAllUnterricht:  function (list) {
      this.recipientsResult = globals.selectOptionsSchuelerAllUnterricht;
      this.recipientsListOpen = list;
    },

    clickHandlerRecipientsSchuelerKlassen:  function (list) {
      this.recipientsResult = globals.selectOptionsSchuelerKlassen;
      this.recipientsListOpen = list;
    },

    clickHandlerRecipientsElternSingel:  function (list) {
      this.recipientsResult = globals.selectOptionsElternSingel;
      this.recipientsListOpen = list;
    },

    clickHandlerRecipientsOwnUnterricht:  function (list) {
      this.recipientsResult = globals.selectOptionsElternOwnUnterricht;
      this.recipientsListOpen = list;
    },

    clickHandlerRecipientsAllUnterricht:  function (list) {
      this.recipientsResult = globals.selectOptionsElternAllUnterricht;
      this.recipientsListOpen = list;
    },

    clickHandlerRecipientsElternKlassen:  function (list) {
      //this.recipientsResult = globals.selectOptionsElternAllUnterricht;
      this.recipientsListOpen = list;
    },

    
    clickHandlerRecipientsSelect: function (recipient) {
      
      //console.log('recipient',recipient);

      if (!recipient.id && !recipient.key) {
        return false;
      }
      if (recipient.key) {
        recipient.id = recipient.key;
        recipient.text = recipient.name;
      }

      var found = false;
      for (var i = 0, len = this.recipientsSelect.length; i < len; i++) {
        if ( this.recipientsSelect[i].key == recipient.id) {
          found = this.recipientsSelect[i];
        }
      }

      if (found) {

        const index = this.recipientsSelect.indexOf(found);
        if (index > -1) {
          this.recipientsSelect.splice(index, 1);
        }

      } else {
        this.recipientsSelect.push({
          key: recipient.id,
          name: recipient.text
        });
      }

      //console.log('recipientsSelect',this.recipientsSelect);

      //this.recipientsSelectString = list;
      //this.messageRecipients = list;

    },

    clickCloseForm: function () {

      var list = '';
      for(let i = 0; i < this.recipientsSelect.length; i++) {
        if(i > 0) list += ";";
        list += this.recipientsSelect[i]['key'];
      }

      //console.log('list: ', list);

      EventBus.$emit('message--form--set-recipient', {
        type: this.type,
        recipientsString: list,
        recipientsArray: this.recipientsSelect
      })

    },



    isRecipientSelected: function (item) {
      for (var i = 0, len = this.recipientsSelect.length; i < len; i++) {
        if ( this.recipientsSelect[i].key == item.id || this.recipientsSelect[i].key == item.key ) {
          return 'selected';
        }
      }
      return false;

    },

    isTabActive: function (tab) {

      if ( this.recipientsTabOpen == tab) {
        return 'active';
      }

      return false;
    },

    isListActive: function (list) {

      if ( this.recipientsListOpen == list) {
        return 'active';
      }

      return false;
    }



  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
