<template>
  <div>


    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <List v-bind:items="items" ></List>

    <ModalItem v-bind:article="article_content"></ModalItem>

  </div>
</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'

import ModalForm from './mixins/ModalForm.vue'
import ModalItem from './mixins/ModalItem.vue'

import List from './components/List.vue'


export default {
  components: {
    Error, Spinner, List, ModalForm, ModalItem
  },
  data() {
    return {
      apiURL: globals.apiURL,
      acl: globals.acl,

      loading: false,
      error: false,

      items: false,
      article_content: false
    };
  },
  created: function () {

    this.loadLists();

  },
  mounted() {

    var that = this;


    EventBus.$on('modal-content--get', data => {

      console.log(data);

      if (!data.article_id) {
        return false;
      }
      this.loading = true;
      var that = this;
      axios.get( this.apiURL+'/getArticle/'+data.article_id)
      .then(function(response){
        if ( response.data ) {
          if (!response.data.error) {

            that.article_content = response.data;

          } else {
            that.error = ''+response.data.msg;
          }
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

    });


  },
  methods: {

    loadLists: function () {
      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getArticles')
      .then(function (response) {
        if (response.data) {
          if (!response.data.error) {

            that.items = response.data;

          } else {
            that.error = '' + response.data.msg;
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
  }

};
</script>

<style>

</style>