<template>
  <div>
    <ul class="flex-row">
      <li class="hidden"></li>
      <li>
        <label>Mo</label>
        <FormToggle :input="data.mo" @change="handlerToggleAcl($event, 'mo')"></FormToggle>
      </li>
      <li>
        <label>Di</label>
        <FormToggle :input="data.di" @change="handlerToggleAcl($event, 'di')"></FormToggle>
      </li>
      <li>
        <label>Mi</label>
        <FormToggle :input="data.mi" @change="handlerToggleAcl($event, 'mi')"></FormToggle>
      </li>
      <li>
        <label>Do</label>
        <FormToggle :input="data.do" @change="handlerToggleAcl($event, 'do')"></FormToggle>
      </li>
      <li>
        <label>Fr</label>
        <FormToggle :input="data.fr" @change="handlerToggleAcl($event, 'fr')"></FormToggle>
      </li>
    </ul>


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
      data: false
    };
  },
  props: {
    form: Object
  },
  created: function () {
    this.data = this.form;
  },
  methods: {
    handlerToggleAcl($event, index) {
      this.data[index] = $event.value;
      this.$emit('change', this.data);
    },
  }


};
</script>

<style>

</style>