<template>
  <div id="app">

    <div v-if="error" class="form-modal-error"> 
      <b>Folgende Fehler sind aufgetreten:</b>
      <ul>
        <li>{{ error }}</li>
      </ul>
    </div>

    <h3>Access Control List</h3>

    <div class="acl">

      <ul>
        <li>
          <h5>Schüler</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.schuelerRead" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.schuelerWrite" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.schuelerDelete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Eltern</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.elternRead" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.elternWrite" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.elternDelete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Lehrer</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.lehrerRead" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.lehrerWrite" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.lehrerDelete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Sonstige</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.noneRead" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.noneWrite" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.noneDelete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Eigentümer</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.owneRead" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.owneWrite" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.owneDelete" true-value="1" false-value="0" />
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
    moduleID: Boolean || Number,
    moduleName: String,
    childID: Boolean || Number
  },
  components: {
    
  },
  data: function () {
    return {
      loading: true,
      error: false,

      module: false,

      acl: {
        id: 0,
        schuelerRead: 0,
        schuelerWrite: 0,
        schuelerDelete: 0,
        elternRead: 0,
        elternWrite: 0,
        elternDelete: 0,
        lehrerRead: 0,
        lehrerWrite: 0,
        lehrerDelete: 0,
        noneRead: 0,
        noneWrite: 0,
        noneDelete: 0,
        owneRead: 0,
        owneWrite: 0,
        owneDelete: 0
      }
    }
  },
  watch: {
    // moduleID: function () {
    //   //if (this.moduleID) {
    //     this.loadAcl();
    //   //}
    // }


    acl: {
      handler(val){
        // do stuff
        //console.log('changed child', this.moduleID, this.moduleName, this.acl.id);
       
        var that = this;
        EventBus.$emit('acl--changed', {
          acl: that.acl,
          moduleName: that.moduleName,
          childID: that.childID
        });

     },
     deep: true
    }
  },
  created: function () {

    this.loadAcl();

  },
  methods: {

    loadAcl: function () {

      if (this.moduleName && !this.moduleID) {
        this.module = this.moduleName;
      } else {
        this.module = this.moduleID;
      }

      // console.log( this.moduleID );
      // console.log( this.moduleName );
      // console.log( 'load', this.module );

      if ( this.moduleID == null) {
        
        const keys = Object.keys(this.acl)
        for (const key of keys) {
          //console.log(key)
          this.acl[key] = 0;
        }

      } else {
console.log('load');
        var that = this;
        that.error = false;
        that.ajaxGet(
          'rest.php/GetAcl/'+this.module,
          {},
          function (response, that) {
            if (response.data.error == true && response.data.msg) {
              that.error = response.data.msg;
            } else {
              if (response.data.acl) {
                that.acl = response.data.acl;
              } 
            }
          }
        );
        
      }
      

      
      

      
      
    },
    handlerSubmit: function () {

      var that = this;

      that.error = false;
      that.ajaxPost(
        'rest.php/SetAcl/'+this.module,
        { acl: this.acl },
        {},
        function (response, that) {
          
          //console.log(response.data);

          if (response.data.error == true && response.data.msg) {
            that.error = response.data.msg;
          } else if (response.data.done == true) {

            that.error = false;

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
