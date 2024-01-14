<template>

  <div class="si-userselect">

    <div v-if="!state">
      <button class="si-btn" v-on:click="handlerOpenForm"><i class="fas fa-plus"></i> Benutzer hinzufügen</button>
    </div>

    <div v-if="state == 'form'">
      <div class="si-userselect-modal" style="top:0;left:0;" v-on:click.self="handlerCloseForm">
        <div class="si-userselect-modal-box" >
          <div class="si-userselect-modal-content">

            <ul class="list">
              <li v-bind:key="index" v-for="(item, index) in  users" v-on:click="handlerSelectUser(item)">
                <div class="vorname">{{item.vorname}}</div>
                <div class="nachname">{{item.nachname}}</div>
              </li>
            </ul>

            <div class="si-form">
              <ul>
                <li>
                  <label>Suche</label>
                  <input type="text" v-model="searchString" v-on:keyup="handlerChange" />
                </li>
                <li>
                  List: {{selected}}
                </li>
                <li>
                  <button class="si-btn" v-on:click="handlerSubmit"><i class="fas fa-plus"></i> Benutzer hinzufügen</button>
                </li>
              </ul>

            </div>

          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>

const axios = require('axios').default;

export default {
  components: {

  },
  data() {
    return {
      loading: false,
      error: false,

      state: false,
      searchString: '',

      users: [],
      selected: []
    };
  },
  props: {
  },
  created: function () {

  },
  methods: {

    handlerSubmit: function () {
      this.$emit('submit', this.selected)
      this.handlerCloseForm();
    },
    handlerSelectUser: function (user) {
      this.selected.push(user);
    },
    handlerChange: function () {

      this.loading = true;
      var that = this;
      that.users = false;
      axios.get( 'rest.php/GetUser/'+this.searchString)
      .then(function(response){
        if ( response.data ) {
          if (!response.data.error) {
            //console.log(response.data);
            that.users = response.data;
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

    },
    handlerCloseForm: function () {
      this.state = false;
    },
    handlerOpenForm: function () {
      this.state = 'form';
    }

  }

};
</script>

<style scoped>

.list {
  height: 40vh;
  overflow-y: auto;
}

.si-form {
  flex: 2;
}
</style>