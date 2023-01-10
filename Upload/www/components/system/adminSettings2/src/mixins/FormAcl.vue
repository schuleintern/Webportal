<template>
  <div>

    <h3 v-if="form.title"><i class="fa fa-user-shield margin-r-m"></i>{{ form.title }}</h3>
    <h3 v-else><i class="fa fa-user-shield margin-r-m"></i> Benutzerrechte</h3>
    <div v-if="form.desc" class="margin-t-l padding-l-s">{{ form.desc }}</div>

    <table class="si-table">
      <thead>
      <tr>
        <th></th>
        <th>Lesen</th>
        <th>Schreiben</th>
        <th>Löschen</th>
      </tr>
      </thead>
      <tbody>
      <tr v-bind:key="index" v-for="(item, index) in form.acl">
        <td>
          <span v-if="index == 'schueler'">Schüler*innen</span>
          <span v-if="index == 'lehrer'">Lehrer*innen</span>
          <span v-if="index == 'eltern'">Eltern</span>
          <span v-if="index == 'none'">Sonstige</span>
        </td>
        <td>
          <FormToggle :disable="item.read" :input="getGroupAcl(index).read"
                      @change="handlerToggleAcl($event, index, 'read')"></FormToggle>
        </td>
        <td>
          <FormToggle :disable="item.write" :input="getGroupAcl(index).write"
                      @change="handlerToggleAcl($event, index, 'write')"></FormToggle>
        </td>
        <td>
          <FormToggle :disable="item.delete" :input="getGroupAcl(index).delete"
                      @change="handlerToggleAcl($event, index, 'delete')"></FormToggle>
        </td>
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
      status: 0,
      newAcl: false
    };
  },
  props: {
    form: Object,
    acl: Object
  },
  created: function () {
    this.newAcl = this.acl;
  },
  methods: {
    handlerToggleAcl($event, index, typ) {
      if (this.newAcl && this.newAcl.groups && this.newAcl.groups[index]) {
        this.newAcl.groups[index][typ] = $event.value;
        this.$emit('change', this.newAcl);
      }
    },
    getGroupAcl(index) {
      if (this.newAcl && this.newAcl.groups && this.newAcl.groups[index]) {
        return this.newAcl.groups[index];
      }
      return false;
    },
  }


};
</script>

<style>

</style>