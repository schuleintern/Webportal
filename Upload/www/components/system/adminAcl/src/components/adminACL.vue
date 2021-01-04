<template>
  <div class="flex-2">

    <div v-show="error" class="form-modal-error">
      <b>Folgende Fehler sind aufgetreten:</b>
      <div>{{error}}</div>
    </div>

    <div v-show="succeed" class="form-modal-succeed">
      <b>{{succeed}}</b>
    </div>

    <div v-if="loading == true" class="overlay">
      <i class="fa fas fa-sync-alt fa-spin"></i>
    </div>


    <h3>Access Control List</h3>

    <div class="acl">

      <ul>
        <li>
          <h5>Schüler</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.schueler.read" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.schueler.write" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.schueler.delete" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Eltern</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.eltern.read" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.eltern.write" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.eltern.delete" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Lehrer</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.lehrer.read" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.lehrer.write" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.lehrer.delete" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Sonstige</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.none.read" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.none.write" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.none.delete" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Eigentümer</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.owne.read" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.owne.write" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.owne.delete" true-value="1" false-value="0" v-on:change="handlerSubmit" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
      </ul>

    </div>

    
  </div>
</template>

<script>
//console.log('globals',globals);

const axios = require('axios').default;

export default {
  name: 'app',
  props: {
  },
  components: {
  },
  data: function () {
    return {
      selfURL: globals.selfURL,
      loading: false,
      error: false,
      succeed: false,

      acl: globals.acl
    }
  },

  created: function () {

},
  methods: {


    handlerSubmit: function () {

      if (!this.selfURL) {
        return false;
      }

      this.error = false;
      this.loading = true;

      var that = this;
      that.ajaxPost(
        this.selfURL+'&task=saveACL',
        { acl: this.acl },
        {},
        function (response, that) {
          
          if (response.data.error == true && response.data.msg) {
            that.error = response.data.msg;
            that.succeed = false;
          } else if (response.data.success == true) {
            that.error = false;
            that.succeed = response.data.msg;
          }
        },
        function () {
          that.error = 'Fehler beim Laden. 02';
          that.succeed = false;
        },
        function () {
          that.loading = false;
        }
      );
    },

    ajaxPost: function (url, data, params, callback, error, allways) {
      this.loading = true;
      var that = this;
      axios.post(url, data, {
        params: params
      })
      .then(function (response) {
        // console.log(response.data);
        if (callback && typeof callback === 'function') {
          callback(response, that);
        }
      })
      .catch(function (resError) {
        //console.log(error);
        if (resError && typeof error === 'function') {
          error(resError);
        }
      })
      .finally(function () {
        // always executed
        if (allways && typeof allways === 'function') {
          allways();
        }
        that.loading = false;
      });  
      
    }

  }
}
</script>

<style>
</style>
