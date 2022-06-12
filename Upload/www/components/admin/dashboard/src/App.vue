<template>
  <div>

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <ModalAdd v-bind:data="modalAddModal" v-bind:addWidgetList="addWidgetList"></ModalAdd>

    <button class="si-btn" v-on:click="handlerAddBox"><i class="fa fa-plus"></i> Hinzufügen</button>
    <grid-layout
        :layout.sync="layout"
        :col-num="colNum"
        :row-height="55"
        :is-draggable="true"
        :is-resizable="true"
        :is-mirrored="false"
        :vertical-compact="false"
        :margin="[10, 10]"
        :use-css-transforms="true"
        :auto-size="true"
        @layout-updated="layoutUpdatedEvent"
    >

      <grid-item v-for="item in layout"
                 :x="item.x"
                 :y="item.y"
                 :w="item.w"
                 :h="item.h"
                 :i="item.i"
                 :minW="item.minW"
                 :minH="item.minH"
                 :key="item.i">
        <h4 class=""><i class="fa fa-th"></i> {{item.title}}</h4>

        <label>Sichtbar:</label>
        <div class="si-btn-multiple">
          <button v-if="item.access.admin == 1" class="si-btn si-btn-off si-btn-small " > Admin</button>
          <button v-if="item.access.adminGroup == 1" class="si-btn si-btn-off si-btn-small " > Moduladmin</button>
          <button v-if="item.access.teacher == 1" class="si-btn si-btn-off si-btn-small " > Lehrer</button>
          <button v-if="item.access.other == 1" class="si-btn si-btn-off si-btn-small " > Mitarbeiter</button>
          <button v-if="item.access.pupil == 1" class="si-btn si-btn-off si-btn-small " > Schüler</button>
          <button v-if="item.access.parents == 1" class="si-btn si-btn-off si-btn-small " > Eltern</button>

          <span v-if="item.access.admin == 0 && item.access.adminGroup == 0 && item.access.teacher == 0 && item.access.pupil == 0 && item.access.parents == 0 && item.access.other == 0" >
            <button class="si-btn si-btn-red si-btn-small " > Unsichtbar !</button>
          </span>
        </div>

        <br>

        <button class="si-btn si-btn-light si-btn-icon" @click="removeItem(item.i)"><i class="fa fa-trash"></i> Entfernen</button>
      </grid-item>
    </grid-layout>

  </div>
</template>

<script>

const axios = require('axios').default;

import VueGridLayout from 'vue-grid-layout';
import Error from './mixins/Error.vue';
import Spinner from './mixins/Spinner.vue';
import ModalAdd from './mixins/ModalAdd.vue';

export default {
  components: {
    Error, Spinner,
    GridLayout: VueGridLayout.GridLayout,
    GridItem: VueGridLayout.GridItem,
    ModalAdd
  },
  data() {
    return {
      selfURL: globals.selfURL,
      error: false,
      loading: false,

      addWidgetList: [],
      list: globals.list,

      layout: [
        //{"x":0,"y":0,"w":2,"h":2,"i":"0"},
        //{"x":2,"y":0,"w":2,"h":4,"i":"1"}
      ],
      colNum: 12,
      index: 0,

      modalAddModal: false,
      noUpdate: false
    };
  },
  created: function () {

    this.layout = this.list;


    EventBus.$on('modalAdd--close', data => {
      this.modalAddModal = false;
    });

    EventBus.$on('modalAdd--add', data => {
      if (data.item && data.item.uniqid && data.item.widget_id) {

        //console.log(data.item);

        this.loading = true;
        this.error = false;
        var that = this;
        axios.get( this.selfURL+'&task=addWidget&uniqid='+data.item.uniqid+'&wid='+data.item.widget_id)
        .then(function(response){

          if ( response.data ) {
            if (response.data.error == true) {
              that.error = response.data.msg;
            } else {
              that.error = false;
              that.modalAddModal = false;
              if (response.data.id && response.data.uniqid) {
                that.addItem(response.data.id, response.data.title, response.data.uniqid, response.data.param);
              }
            }
          } else {
            that.error = 'Fehler beim Speichern. 01';
          }
        })
        .catch(function(){
          that.error = 'Fehler beim Speichern. 02';
        })
        .finally(function () {
          that.loading = false;
        });

      }
    });


  },
  mounted() {
    // this.$gridlayout.load();
    this.index = this.layout.length;
  },
  methods: {
    handlerAddBox: function () {

      this.loading = true;
      this.error = false;
      var that = this;
      axios.get( this.selfURL+'&task=formWidgets')
          .then(function(response){

            if ( response.data ) {
              if (response.data.error == true) {
                that.error = response.data.msg;
              } else {
                that.error = false;
                //console.log(response.data);
                if (response.data.list) {
                  that.modalAddModal = true;
                  that.addWidgetList = response.data.list;
                }
              }
            } else {
              that.error = 'Fehler beim Speichern. 01';
            }
          })
          .catch(function(){
            that.error = 'Fehler beim Speichern. 02';
          })
          .finally(function () {
            that.loading = false;
          });



    },
    addItem: function (id, title, uniqid, param ) {
      // Add a new item. It must have a unique key!
      //console.log('add',id,uniqid,param);

      this.noUpdate = true;
      this.layout.push({
        x: (this.layout.length * 2) % (this.colNum || 12),
        y: this.layout.length + (this.colNum || 12), // puts it at the bottom
        w: param.w || 4,
        h: param.h || 2,
        i: id,
        minW: param.minW,
        minH: param.minH,
        uniqid: uniqid,
        title: title,
        access: param.access,
      });


    },
    removeItem: function (val) {

      const formData = new FormData();
      formData.append('id', val);


      this.loading = true;
      this.error = false;
      var that = this;
      axios.post( this.selfURL+'&task=removeWidget', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){

        if ( response.data ) {
          if (response.data.error == true) {
            that.error = response.data.msg;
          } else {
            that.error = false;

            that.noUpdate = true;
            // Entferne aus Grid
            const index = that.layout.map(item => item.i).indexOf(val);
            that.layout.splice(index, 1);

          }
        } else {
          that.error = 'Fehler beim Speichern. 01';
        }
      })
      .catch(function(){
        that.error = 'Fehler beim Speichern. 02';
      })
      .finally(function () {
        that.loading = false;
      });

    },
    layoutUpdatedEvent: function(newLayout){

      //console.log("Updated layout: ", newLayout)

      if (this.noUpdate == true) {
        this.noUpdate = false;
        return false;
      }

      var newLayout = JSON.stringify(newLayout);

      if (newLayout == '[]') {
        return false;
      }
      const formData = new FormData();
      formData.append('layout', newLayout);

      this.loading = true;
      this.error = false;
      var that = this;
      axios.post( this.selfURL+'&task=saveLayout', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){

        if ( response.data ) {
          if (response.data.error == true) {
            that.error = response.data.msg;
          } else {
            that.error = false;

          }
        } else {
          that.error = 'Fehler beim Speichern. 01';
        }
      })
      .catch(function(){
        that.error = 'Fehler beim Speichern. 02';
      })
      .finally(function () {
        that.loading = false;
      });

    }
  }

};
</script>

<style>

.vue-grid-item {
  box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 15px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px;
  border-radius: 1rem;
  padding: 1rem;
  margin-bottom: 0.6rem;
  margin-top: 0.6rem;
  text-overflow: ellipsis;
  white-space: nowrap;
  border: 1px solid #b7c7ce;
  color: #000;
  text-align: left;
  overflow: auto;

  -webkit-touch-callout: none; /* iOS Safari */
  -webkit-user-select: none; /* Safari */
  -khtml-user-select: none; /* Konqueror HTML */
  -moz-user-select: none; /* Old versions of Firefox */
  -ms-user-select: none; /* Internet Explorer/Edge */
  user-select: none; /* Non-prefixed version, currently
                                  supported by Chrome, Edge, Opera and Firefox */
}



.vue-grid-item.vue-grid-placeholder {
  background: #8aa4af;
  opacity: 0.2;
  transition-duration: 100ms;
  z-index: 2;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  -o-user-select: none;
  user-select: none;
}

</style>