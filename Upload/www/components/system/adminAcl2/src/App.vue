<template>

  <div class="">

    <AjaxNotif v-bind:notif="notif"></AjaxNotif>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <div class="flex-row">
      <div class="flex-8">
        <FormAcl v-if="form && acl" :form="form" :acl="acl" @change="handlerChangeAcl"></FormAcl>
      </div>
      <div class="flex-1"></div>
      <div class="flex-4">
        <FormAdmin :adminList="adminList" :adminExtension="adminExtension" @submit="handlerChangeAdmins"></FormAdmin>
      </div>
    </div>


  </div>
</template>

<script>

import AjaxNotif from './mixins/AjaxNotif.vue'
import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'
import FormAcl from './mixins/FormAcl.vue'
import FormAdmin from './mixins/FormAdmin.vue'


const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    AjaxError, AjaxSpinner, AjaxNotif,
    FormAcl, FormAdmin
  },
  data() {
    return {
      selfURL: window.globals.selfURL,
      error: false,
      loading: false,
      acl: window.globals.acl,
      form: window.globals.form,
      adminList: window.globals.adminList,
      adminExtension: window.globals.adminExtension,

    };
  },
  created() {

  },
  methods: {

    handlerChangeAdmins(data) {

      if (!data) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('userlist', JSON.stringify(data));

      this.loading = true;
      var that = this;
      axios.post(this.selfURL + '&task=saveAdminAdmins', formData)
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = response.data.error;
              } else {
                if (response.data.success == true) {
                  that.$bus.$emit('notif--open', {
                    msg: response.data.msg
                  });
                  that.adminExtension = data;
                }
              }
            } else {
              that.error = 'Fehler beim Speichern. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Speichern. 02';
          })
          .finally(function () {
            that.loading = false;
          });


    },
    handlerChangeAcl(newACL) {
      if (newACL) {
        this.acl = newACL;
        this.submitAcl();
      }
    },

    submitAcl() {

      if (!this.acl) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('acl', JSON.stringify(this.acl));

      this.loading = true;
      var that = this;
      axios.post(this.selfURL + '&task=saveAdminACL', formData)
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                if (response.data.success == true) {
                  that.$bus.$emit('notif--open', {
                    msg: response.data.msg
                  });
                }
              }
            } else {
              that.error = 'Fehler beim Speichern. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Speichern. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });

    }

  }
}
</script>

<style>

</style>
