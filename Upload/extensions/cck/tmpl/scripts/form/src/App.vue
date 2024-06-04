<template>
  <div>


    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <h2>{{item.title}}</h2>
    <p>{{item.template}}</p>

    {{item.fields}}

    <div class="si-form">
      <ul>
        <li>
          <label>Titel</label>
          <input type="text" v-model="item.article_title" />
        </li>
        <li v-bind:key="index" v-for="(field, index) in  item.fields">
          <label>{{field.title}}</label>
          <input type="text" v-model="field.content" />
        </li>
        <li>
          <button class="si-btn" v-on:click="handlerSubmit">Speichern</button>
        </li>
      </ul>
    </div>

  </div>
</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'


export default {
  components: {
    Error, Spinner
  },
  data() {
    return {
      apiURL: globals.apiURL,
      acl: globals.acl,

      loading: false,
      error: false,

      item: globals.item

    };
  },
  created: function () {



  },
  mounted() {



  },
  methods: {

    handlerSubmit: function () {

      console.log(this.item);

      if (!this.item.id || !this.item.article_title || this.item.fields.length < 1) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('form_id', this.item.id);
      formData.append('title', this.item.article_title);
      formData.append('fields', JSON.stringify(this.item.fields) );



      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setArticle', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.insert) {

            console.log('ok!');

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



    }
  }

};
</script>

<style>

</style>