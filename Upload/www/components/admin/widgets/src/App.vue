<template>
  <div>

    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>


    <div v-if="tab=='form'" class="padding-t-l">
      <Form v-bind:item="form"></Form>
    </div>

    <div v-if="tab=='list'">
      <h3><i class="fa fas fa-chart-pie"></i> Verfügbare Widgets</h3>
      <div v-bind:key="index" v-for="(extension, index) in  list" class="padding-t-l">
        <h4><i class="fa fas " :class="extension.icon"></i> {{extension.title}}</h4>

        <table class="si-table si-table-style-firstLeft si-table-style-firstWidth-m ">
          <thead>
          <tr>
            <td>Name</td>
            <td>Position</td>
            <td>Aktiviert</td>
            <td>Sichtbarkeit</td>

          </tr>
          </thead>
          <tbody>
          <tr v-bind:key="index" v-for="(item, index) in  extension.widgets" class="line-oddEven">
            <td><a v-on:click="handlerFormShow(item)" class="">{{item.title}} <div class="text-small text-grey">{{item.uniqid}}</div></a></td>
            <td><span class="text-grey">{{item.position}}</span></td>
            <td>
              <button
                  v-if="item.status == 1"
                  v-on:click="handlerToggleActive(item)"
                  class="si-btn si-btn-toggle-on"><i class="fas fa-toggle-on"></i> An</button>
              <button
                  v-if="item.status == 0"
                  v-on:click="handlerToggleActive(item)"
                  class="si-btn si-btn-toggle-off"><i class="fas fa-toggle-off"></i> Aus</button>
            </td>
            <td v-on:click="handlerFormShow(item)">
              <button v-if="item.access.admin == 1" class="si-btn si-btn-off si-btn-small margin-r-s" > Admin</button>
              <button v-if="item.access.adminGroup == 1" class="si-btn si-btn-off si-btn-small margin-r-s" > Moduladmin</button>
              <button v-if="item.access.teacher == 1" class="si-btn si-btn-off si-btn-small margin-r-s" > Lehrer</button>
              <button v-if="item.access.other == 1" class="si-btn si-btn-off si-btn-small margin-r-s" > Mitarbeiter</button>
              <button v-if="item.access.pupil == 1" class="si-btn si-btn-off si-btn-small margin-r-s" > Schüler</button>
              <button v-if="item.access.parents == 1" class="si-btn si-btn-off si-btn-small margin-r-s" > Eltern</button>
              <span v-if="item.access.admin == 0 && item.access.adminGroup == 0 && item.access.teacher == 0 && item.access.pupil == 0 && item.access.parents == 0 && item.access.other == 0" >
                <button v-if="item.status == 1" class="si-btn si-btn-red si-btn-small margin-r-s" > Unsichtbar !</button>
              </span>

            </td>

          </tr>
          </tbody>
        </table>

      </div>
    </div>




  </div>
</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'

import Form from './components/Form.vue'

export default {
  components: {
    Error, Spinner,
    Form
  },
  data() {
    return {
      selfURL: globals.selfURL,
      error: false,
      loading: false,
      tab: 'list',

      list: false, // from Ajax

      form: false

    };
  },
  created: function () {
    this.loadList();

    EventBus.$on('show--list', data => {
      this.form = false;
      this.tab = 'list';
    });

    EventBus.$on('item-form--submit', data => {
      console.log(data);
      if (!data.item.uniqid) {
        return false;
      }
      this.loading = true;
      var that = this;
      var formData = new FormData();
      formData.append("id", data.item.id || false );
      formData.append("uniqid", data.item.uniqid || '' );
      formData.append("position", data.item.position || '' );
      formData.append("access", JSON.stringify(data.item.access) || '' );
      axios.post(this.selfURL+'&task=item-submit&id='+data.item.id, formData)
        .then(function (response) {
          //console.log(response);
          if ( response.data ) {
            if (response.data.error != true) {
              that.loadList();
              that.tab = 'list';
              that.form = false;
            } else {
              that.error = response.data.msg;
            }
          } else {
            that.error = 'Fehler beim Laden. 01';
          }
        })
        .catch(function (error) {
          that.error = 'Fehler beim Laden. 02';
        }).finally(function () {
          // always executed
          that.loading = false;
        });
    });


  },
  methods: {

    handlerToggleAccess: function (val) {

      //console.log(this.form.access);

      if ( this.form.access[val] == 1) {
        this.form.access[val] = 0;
      } else {
        this.form.access[val] = 1;
      }

    },

    handlerFormShow: function (item) {

      this.form = item;
      this.tab = 'form';

    },

    loadList: function () {

      this.loading = true;

      var that = this;
      axios.get( this.selfURL+'&task=api-list')
      .then(function(response){
        
        if ( response.data ) {
          that.list = response.data;
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
        
      })
      .catch(function(){
        that.error = 'Fehler beim Laden. 02';
      })
      .finally(function () {
        // always executed
        that.loading = false;
      }); 

    },
    handlerToggleActive: function (item) {

      if (!item.uniqid) {
        return false;
      }

      this.error = false;

      const formData = new FormData();
      formData.append('uniqid', item.uniqid);
      formData.append('position', item.position);
      formData.append('access', JSON.stringify(item.access));

      this.loading = true;
      var that = this;
      axios.post( this.selfURL+'&task=api-toggle-active', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data )  {
          if (response.data.error == true) {
            that.error = response.data.msg;
          } else {
            that.error = false;
            that.loadList();
          }
        } else {
          that.error = 'Fehler beim Aktivieren. 01';
        }
      })
      .catch(function(){
        that.error = 'Fehler beim Aktivieren. 02';
      })
      .finally(function () {
        // always executed
      }); 

    }
  }

};
</script>

<style>

</style>