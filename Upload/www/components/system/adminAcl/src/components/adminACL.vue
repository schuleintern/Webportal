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


    <h3><i class="fa fa-user-shield margin-r-m"></i>Benutzerrechte</h3>

    <div class="margin-r-xxl">
      <table class="table_1">
        <thead>
          <tr>
            <td></td>
            <td>Lesen</td>
            <td>Schreiben</td>
            <td>Löschen</td>
          </tr>
        </thead>
        <tbody class="oddEven">
          <tr class="">
            <td>Schüler</td>
            <td>
              <input type="checkbox" v-model="acl.groups.schueler.read" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
            <td>
              <input type="checkbox" v-model="acl.groups.schueler.write" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
            <td>
              <input type="checkbox" v-model="acl.groups.schueler.delete" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
          </tr>
          <tr class="">
            <td>Eltern</td>
            <td>
              <input type="checkbox" v-model="acl.groups.eltern.read" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
            <td>
              <input type="checkbox" v-model="acl.groups.eltern.write" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
            <td>
              <input type="checkbox" v-model="acl.groups.eltern.delete" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
          </tr>
          <tr class="">
            <td>Lehrer</td>
            <td>
              <input type="checkbox" v-model="acl.groups.lehrer.read" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
            <td>
              <input type="checkbox" v-model="acl.groups.lehrer.write" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
            <td>
              <input type="checkbox" v-model="acl.groups.lehrer.delete" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
          </tr>
          <tr class="">
            <td>Sonstige</td>
            <td>
              <input type="checkbox" v-model="acl.groups.none.read" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
            <td>
              <input type="checkbox" v-model="acl.groups.none.write" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
            <td>
              <input type="checkbox" v-model="acl.groups.none.delete" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
          </tr>
          <tr class="">
            <td>Eigentümer</td>
            <td>
              <input type="checkbox" v-model="acl.groups.owne.read" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
            <td>
              <input type="checkbox" v-model="acl.groups.owne.write" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
            <td>
              <input type="checkbox" v-model="acl.groups.owne.delete" true-value="1" false-value="0" v-on:change="handlerSubmit" />
            </td>
          </tr>
        </tbody>
      </table>
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
