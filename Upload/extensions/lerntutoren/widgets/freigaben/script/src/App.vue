<template>
  <div class="">


    <a class="si-btn si-btn-border" href="index.php?page=ext_lerntutoren&view=custom&admin=true">
      <i class="fa fa-graduation-cap"></i> Lerntutoren <span v-if="count" class="margin-l-l label bg-red">{{count}}</span>
    </a>

    <table class="si-table si-table-small">
      <thead>
      <tr>
        <td>Name</td>
        <td>Jahrgangsstufe</td>
        <td>Stunden</td>
        <td>Fach</td>
        <td></td>
      </tr>
      </thead>
      <tbody>
      <tr v-if="list && list.length >= 1" v-bind:key="index" v-for="(item, index) in  list"
          class="">
        <td>{{item.user.name}}</td>
        <td>{{item.jahrgang}}</td>
        <td>{{item.einheiten}}</td>
        <td>{{item.fach}}</td>
        <td>
          <button v-if="item.status == 'created'" class="si-btn si-btn-green si-btn-icon" v-on:click="handlerFreigeben(item, index)"><i class="fas fa-unlock"></i></button>
        </td>

      </tr>
      <tr v-if="list.length == 0">
        <td colspan="5"> - keine Inhalte vorhanden -</td>
      </tr>

      </tbody>
    </table>


  </div>
</template>

<script>

const axios = require('axios').default;

export default {
  components: {
  },
  data() {
    return {

      apiURL: globals_widget_lerntutoren_freigaben.apiURL,
      count: globals_widget_lerntutoren_freigaben.count,
      list: globals_widget_lerntutoren_freigaben.tutoren

    };
  },
  created: function () {

  },
  methods: {

    handlerFreigeben: function (item, index) {

      const formData = new FormData();
      formData.append('id', item.id);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/openAdmin', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error == false) {
            //that.loadList();
            that.list.splice(index,1);
            that.count--;

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