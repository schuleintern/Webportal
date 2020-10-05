<template>
  <div id="app">

    <div v-if="error" class="form-modal-error"> 
      <b>Folgende Fehler sind aufgetreten:</b>
      <ul>
        <li>{{ error }}</li>
      </ul>
    </div>

    <div v-if="success" class="form-modal-success"> 
      {{ success }}
    </div>

    <h3>Access Control List</h3>

    <div class="acl">

      <ul class="" v-if="acl.aclID >= 0">
        <li>
          <h5>Schüler</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.schueler.read" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.schueler.write" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.schueler.delete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Eltern</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.eltern.read" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.eltern.write" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.eltern.delete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Lehrer</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.lehrer.read" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.lehrer.write" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.lehrer.delete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Sonstige</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.none.read" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.none.write" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.none.delete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Eigentümer</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.owne.read" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.owne.write" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.owne.delete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
      </ul>

      <button v-on:click="handlerSubmit">Speichern</button>

    </div>

  </div>
</template>

<script>
//console.log('globals',globals);

const axios = require('axios').default;

export default {
  name: 'app',
  props: {
    moduleName: String
  },
  components: {
    
  },
  data: function () {
    return {
      loading: true,
      error: false,
      success: false,

      //module: false,

      acl: {}
    }
  },
  watch: {
    // moduleID: function () {
    //   //if (this.moduleID) {
    //     this.loadAcl();
    //   //}
    // }


    // acl: {
    //   handler(val){
    //     // do stuff
    //     //console.log('changed child', this.moduleID, this.moduleName, this.acl.id);
       
    //     var that = this;
    //     EventBus.$emit('acl--changed', {
    //       acl: that.acl,
    //       moduleName: that.moduleName,
    //       childID: that.childID
    //     });

    //  },
    //  deep: true
    // }
  },
  created: function () {

    this.loadAcl();

  },
  methods: {

    loadAcl: function () {

      // if (this.moduleName && !this.moduleID) {
      //   this.module = this.moduleName;
      // } else {
      //   this.module = this.moduleID;
      // }

      // console.log( this.moduleID );
      // console.log( this.moduleName );
      // console.log( 'load', this.module );

      // if ( this.moduleID == null) {
        
      //   const keys = Object.keys(this.acl)
      //   for (const key of keys) {
      //     //console.log(key)
      //     this.acl[key] = 0;
      //   }

      // } else {

        var that = this;
        that.error = false;
        that.ajaxGet(
          'rest.php/GetAcl/'+this.moduleName,
          {},
          function (response, that) {
            if (response.data.error == true && response.data.msg) {
              that.error = response.data.msg;
              //console.log(response.data.aclBlank);
              that.acl = response.data.aclBlank;
            } else {
              if (response.data.acl) {
                that.acl = response.data.acl;
              } 
            }
          }
        );
        
      //}
      

      
      

      
      
    },
    handlerSubmit: function () {

      var that = this;

      that.error = false;
      that.ajaxPost(
        'rest.php/SetAcl/'+this.moduleName,
        { acl: this.acl },
        {},
        function (response, that) {
          
          //console.log(response.data);

          if (response.data.error == true && response.data.msg) {
            that.error = response.data.msg;
          } else if (response.data.success == true) {

            that.error = false;
            that.success = response.data.msg;
            that.acl.aclID = response.data.aclID;
          }

        }
      );

    },
    ajaxGet: function (url, params, callback, error, allways) {
      this.loading = true;
      var that = this;
      axios.get(url, {
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
