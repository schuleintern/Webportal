<template>

  <div class="">

    <AjaxNotif v-bind:notif="notif"></AjaxNotif>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <h3 class="margin-b-l"><i class="fa fa-sliders-h"></i> Einstellungen</h3>

    <ul class="noListStyle padding-t-l form-style-2">

      <li v-bind:key="index" v-for="(item, index) in settings"
          class="padding-t-m  padding-b-m line-oddEven">

        <div class="flex-row">

          <div class="flex-1 padding-r-l">
            <div>{{ item.title }}</div>
            <div class="text-small text-grey">{{ item.desc }}</div>
          </div>
          <div class="flex-2">

            <FormRules
                v-if="item.typ == 'ACL'"
                :input="item.value"
                @done="triggerToggleValue($event, item)"></FormRules>

            <FormToggle
                v-else-if="item.typ == 'BOOLEAN'"
                :input="item.value"
                @change="triggerToggleValue($event, item)"></FormToggle>

            <FormNumber
                v-else-if="item.typ == 'NUMBER'"
                :input="item.value"
                @submit="triggerToggleValue($event, item)"></FormNumber>

            <FormSelect
                v-else-if="item.typ == 'SELECT'"
                :input="item.value"
                :options="item.options"
                @submit="triggerToggleValue($event, item)"></FormSelect>

            <FormText
                v-else-if="item.typ == 'HTML' || item.typ == 'TEXT'"
                :input="item.value"
                @submit="triggerToggleValue($event, item)"></FormText>

            <!--   v-if="item.typ == 'STRING'"-->
            <FormInput
                v-else
                :input="item.value"
                @submit="triggerToggleValue($event, item)"></FormInput>

          </div>
        </div>



      </li>
    </ul>


  </div>
</template>

<script>

import AjaxNotif from './mixins/AjaxNotif.vue'
import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

import FormInput from './mixins/FormInput.vue'
import FormSelect from './mixins/FormSelect.vue'
import FormText from './mixins/FormText.vue'
import FormToggle from './mixins/FormToggle.vue'
import FormNumber from './mixins/FormNumber.vue'
import FormRules from './mixins/FormRules.vue'

const axios = require('axios').default;


export default {

  name: 'App',
  components: {
    AjaxError, AjaxSpinner, AjaxNotif,
    FormInput, FormSelect, FormText, FormToggle, FormNumber, FormRules
  },
  data() {
    return {
      selfURL: window.globals.selfURL,
      settings: window.globals.settings,
      error: false,
      loading: false

    };
  },
  created() {

  },
  methods: {

    triggerToggleValue(data, item) {

      console.log('triggerToggleEvent', 'neu:',data.value, item);
      item.value = data.value;
      this.saveData();

    },
    saveData() {

      if (!this.settings) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('settings', JSON.stringify(this.settings));

      this.loading = true;
      var that = this;
      axios.post(this.selfURL + '&task=saveAdminSettings', formData)
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = response.data.error;
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
            that.loading = false;
          });
    }

  }
}
</script>

<style>

</style>
