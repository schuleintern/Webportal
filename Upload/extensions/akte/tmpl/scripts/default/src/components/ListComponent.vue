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
          <li class="flex-row">
            <div class="">
              <textarea class="si-textarea width-40vw" @keyup="handlerForm" v-model="form.text" placeholder="Notiz hinzufügen ..."></textarea>
            </div>
            <div v-if="showForm" class="flex-1 padding-l-l">
              <button v-bind:key="i" v-for="(item, i) in  vorlagenObj" class="blockInline si-btn si-btn-border si-btn-small margin-r-s"
                      @click="handlerVorlage(item)" >{{item}}</button>
            </div>
          </li>

          <li v-if="showForm && tags && tags.length > 0" class="padding-t-m">
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
              <div class="flex-row">
                <div class="flex-1">
                  <div class="si-btn-multiple">
                    <div v-if="item.count" class="si-btn si-btn-off si-btn-small">{{item.count}}</div>
                    <button v-if="acl.write == 1" @click="handlerCountAdd(item)" class="si-btn si-btn-icon si-btn-small si-btn-green"><i class="fa fa-user-plus"></i></button>
                  </div>
                </div>
                <div class="flex-1">
                  <div class="si-btn si-btn-off si-btn-small margin-r-s" v-bind:key="t" v-for="(tag, t) in  item.tags">{{showTag(tag)}}</div>
                </div>
                <div class="text-grey text-small flex-1 flex-row flex-center-center-center ">
                  <span class="margin-r-l"><i class="fa fa-clock"></i> {{item.createdTime}}</span>
                  <span><i class="fa fa-user"></i> {{item.createdUser.name}}</span>
                  <button v-if="acl.delete == 1" @click="handlerDelete(item)" class="margin-l-m si-btn si-btn-icon si-btn-small si-btn-border"><i class="fa fa-trash"></i></button>
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
      vorlagen: window.globals.vorlagen,
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

    vorlagenObj() {
      return this.vorlagen.split(';').map(s => s.substr(0,15)+'...');
      //return this.vorlagen.split(';');
    }
  },
  created: function () {



  },
  methods: {

    handlerVorlage(str) {

      if (str) {
        if (this.form.text && this.form.text.length > 0) {
          if ( confirm('Den vorhanden Text überschreiben?') == true ) {
            this.form.text = str;
          }
        } else {
          this.form.text = str;
        }
      }
    },
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
        this.loadItems(this.selectedPupil.id);
      }

    },
    loadItems(id) {
      if (!id) {
        return false;
      }
      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getItem/'+id)
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
    },

    handlerCountAdd(item) {

      if (!item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', item.id );

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/addItemCount', formData)
      .then(function (response) {
        if (response.data) {

          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            if (!item.count) {
              item.count = 0;
            }
            item.count = item.count +1;
          }
        } else {
          that.error = 'Fehler beim Speichern. 01';
        }
      })
      .catch(function () {
        that.error = 'Fehler beim Speichern. 02';
      })
      .finally(function () {
        // always executed
        that.loading = false;
      });


    },
    handlerDelete(item) {
      if (confirm("Den Eintrag endgültig löschen?") == true) {

        if (!item.id) {
          console.log('missing');
          return false;
        }

        const formData = new FormData();
        formData.append('id', item.id );

        this.loading = true;
        var that = this;
        axios.post(this.apiURL + '/deleteItem', formData)
            .then(function (response) {
              if (response.data) {

                if (response.data.error) {
                  that.error = '' + response.data.msg;
                } else {
                  that.loadItems(that.selectedPupil.id);
                }
              } else {
                that.error = 'Fehler beim Speichern. 01';
              }
            })
            .catch(function () {
              that.error = 'Fehler beim Speichern. 02';
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