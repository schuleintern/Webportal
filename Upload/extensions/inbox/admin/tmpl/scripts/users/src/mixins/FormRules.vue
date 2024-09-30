<template>
  <div>

    <table class="si-table " style="margin: 0;">
      <thead>
        <tr>
        <td>Schüler*in</td>
        <td>Eltern</td>
        <td>Lehrer*in</td>
        <td>Sonstige</td>
      </tr>
      </thead>
      <tbody>
        <tr>
          <td><FormToggle :input="form.isPupil" @change="triggerToggleValue($event, 'isPupil')"></FormToggle></td>
          <td><FormToggle :input="form.isEltern" @change="triggerToggleValue($event, 'isEltern')"></FormToggle></td>
          <td><FormToggle :input="form.isTeacher" @change="triggerToggleValue($event, 'isTeacher')"></FormToggle></td>
          <td><FormToggle :input="form.isNone" @change="triggerToggleValue($event, 'isNone')"></FormToggle></td>
        </tr>
     </tbody>
    </table>
     
  </div>
</template>

<script>

import FormToggle from './FormToggle.vue'

export default {
  components: {
    FormToggle
  },
  data() {
    return {

      form: {

      }
    };
  },
  props: {
    input: Object,
    acl: Object
  },
  created: function () {
    //this.newAcl = this.acl;
    //console.log(this.input);
    if (this.input) {
      this.form = JSON.parse(this.input);
    }
  },
  methods: {

    triggerToggleValue(data, item) {

      //console.log('triggerToggleEvent', 'neu:',data.value, item);
      this.form[item] = data.value;

      //console.log(JSON.stringify(this.form));

      this.$emit('done', {'value': JSON.stringify(this.form)} );
      this.$emit('change', {'value': this.form} );

    },





  }


};
</script>

<style>

</style>