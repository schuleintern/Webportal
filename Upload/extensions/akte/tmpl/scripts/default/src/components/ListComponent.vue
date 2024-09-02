<template>
  <div class="flex-row">

    <div class="flex-1 ">
      <div class="klassen" v-if="!selectedKlasse">
        <h4><i class="fa fa-users"></i>  Klassen</h4>
        <ul class="noListStyle ">
          <li v-bind:key="index" v-for="(item, index) in  klassen" class="">
            <button class="si-btn width-100p" @click="handlerSelectKlasse(item)" >{{ item.name }}</button>
          </li>
        </ul>
      </div>
      <div class="pupils" v-if="selectedKlasse">
        <button class="si-btn si-btn-light" @click="handlerBackKlasse"><i class="fa fa-arrow-left"></i> Zurück</button>

        <h3><i class="fa fa-users"></i> Klasse {{ selectedKlasse.name }}</h3>
        <ul class="noListStyle">
          <li v-bind:key="index" v-for="(item, index) in  selectedKlasse.pupils" class="">
            <button class="si-btn width-100p" :class="{'si-btn-active': selectedPupil.id == item.id }" @click="handlerSelectPupil(item)">{{ item.name }}</button>
          </li>
        </ul>
      </div>
    </div>
    <div class="flex-5 padding-l-l">

      <div class="content" v-if="selectedPupil">

        <div class="">
          <h2>{{selectedPupil.name}} - {{selectedPupil.klasse}}</h2>
        </div>

        <ul class="noListStyle">
          <li>
            <textarea class="si-textarea width-40vw" @keyup="handlerForm" v-model="form.text" placeholder="Notiz hinzufügen ..."></textarea>
          </li>
          <li v-if="showForm" class="padding-t-m">
            <label>Schlagworte</label>
            <div class="si-btn-multiple">
              <button v-bind:key="i" v-for="(item, i) in  tags" class="si-btn si-btn-light margin-r-s"
                      :class="{'si-btn-active': isTag(item.id)}" @click="handlerTag(item)" >{{item.title}}</button>
            </div>
          </li>
          <li class="padding-t-s">
            <button v-if="showForm" class="si-btn si-btn-green width-20rem" @click="handlerSubmit"><i class="fa fa-save"></i> Speichern</button>
          </li>
        </ul>



        <div class="margin-t-l">

          <span v-if="list.length > 0">
            <div v-bind:key="i" v-for="(item, i) in list" class="si-box">
              <div class="padding-t-m padding-b-m">{{item.text}}</div>
              <div class=" flex-row">
                <div class="flex-1">
                  <div class="si-btn si-btn-off si-btn-small margin-r-s" v-bind:key="t" v-for="(tag, t) in  item.tags">{{showTag(tag)}}</div>
                </div>
                <div class="text-grey text-small flex-1 flex-row flex-end-end">
                  <span class="margin-r-l"><i class="fa fa-clock"></i> {{item.createdTime}}</span>
                  <span><i class="fa fa-user"></i> {{item.createdUser.name}}</span>
                </div>
              </div>
            </div>
          </span>
          <div v-else>- noch keine Inhalte -</div>


        </div>

      </div>
    </div>


  </div>
</template>

<script>


const axios = require('axios').default;

export default {
  name: 'ListComponent',
  data() {
    return {
      apiURL: window.globals.apiURL,
      selectedKlasse: false,
      selectedPupil: false,
      showForm: false,

      list: false,
      form: {}

    };
  },
  props: {
    acl: Array,
    klassen: Array,
    tags: Array
  },
  computed: {

  },
  created: function () {



  },
  methods: {

    showTag(itemID) {

      let found = false;
      if (this.tags) {
        this.tags.forEach((obj) => {
          if (obj.id == parseInt(itemID)) {
            found = obj;
          }
        })
      }
      if (found && found.title) {
        return found.title;
      }
      return false;
    },
    isTag(itemID) {
      if (this.form.tags && this.form.tags.indexOf( String(itemID) ) >= 0) {
        return true;
      }
      return false;
    },
    handlerTag(val) {
      if (!this.form.tags) {
        this.form.tags = [];
      }
      const index = this.form.tags.indexOf( String(val.id) );
      if ( index >= 0 ) {
        this.form.tags.splice( index , 1)
      } else {
        this.form.tags.push( String(val.id) );
      }

    },
    handlerForm() {
      this.showForm = true;
    },
    handlerSubmit() {
      const that = this;
      this.$bus.$emit('item--submit', {
        item: this.form,
        itemID: this.selectedPupil.id,
        callback: () => {
          that.form = {};
          that.handlerSelectPupil(this.selectedPupil);
        }
      });
    },
    handlerBackKlasse() {
      this.selectedKlasse = false;
    },
    handlerSelectKlasse(item) {
      this.selectedKlasse = item;
    },
    handlerSelectPupil(item) {

      this.selectedPupil = item;
      this.showForm = false;

      if (this.selectedPupil.id) {

        this.loading = true;
        var that = this;
        axios.get(this.apiURL + '/getItem/'+this.selectedPupil.id)
            .then(function (response) {
              if (response.data) {
                if (response.data.error) {
                  that.error = '' + response.data.msg;
                } else {
                  that.list = response.data;
                }
              } else {
                that.error = 'Fehler beim Laden. 01';
              }
            })
            .catch(function () {
              that.error = 'Fehler beim Laden. 02';
            })
            .finally(function () {
              // always executed
              that.loading = false;
            });

      }



    },


    handlerOpen(item) {

      this.$bus.$emit('page--open', {
        page: 'item',
        item: item
      });

    },


  }


};
</script>

<style>

</style>