<template>
  <div class="" >

    <h3 class="box-title"><i class="fas fa-shopping-cart"></i> Lerntutor buchen</h3>

    <div class="si-hinweis" v-if="LANG_show_text" v-html="LANG_show_text"></div>

    <div class="width-30vw item">

      <User v-if="data.user" v-bind:data="data.user" ></User>

      <table class="si-table">
        <tbody>
          <tr>
            <td><label>Fach</label></td>
            <td>{{data.fach}}</td>
          </tr>
          <tr>
            <td><label>Jahrgang</label></td>
            <td>{{data.jahrgang}}</td>
          </tr>
          <tr>
            <td><label>Stunden</label></td>
            <td>noch {{data.diff}} von {{data.einheiten}}</td>
          </tr>
        </tbody>
      </table>


      <div class="si-form">
        <ul>
          <li>
            <label>Wie viele Nachhilfestunden sollen gebucht werden? </label>
            <select v-model="formEinheiten">
              <option v-bind:key="i" v-for="(o, i) in data.diff">{{o}}</option>
            </select>
          </li>
          <li>
            Du wirst nach der Buchung automatisch weitergeleitet und kannst direkt Kontakt mit deinem Lerntutor aufnehmen.
          </li>
          <li>
            <button class="si-btn" v-on:click="handlerSubmit"><i class="fas fa-shopping-cart"></i> Verbindlich Buchen</button>
          </li>
        </ul>
      </div>
    </div>



  </div>
</template>

<script>

import User from '../mixins/User.vue'
const axios = require('axios').default;

export default {
  components: {
    User
  },
  data() {
    return {
      formEinheiten: 1,

      LANG_show_text: globals.LANG_show_text
    };
  },
  props: {
      data: Object
  },
  created: function () {



  },
  methods: {

    handlerSubmit: function () {
      //console.log(this.data);

      EventBus.$emit('item-submit', {
        einheiten: this.formEinheiten,
        id: this.data.id
      });
    }

  }

};
</script>

<style>
  .item {
    display: flex;
    flex-direction: column;
  }
  .item .si-user {
    align-self: flex-end;
  }
</style>