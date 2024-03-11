<template>

  <span v-if="data && typ == 'boolean'">

    <button v-if="data == 1" class="si-btn si-btn-off" >
      <i class="fa fas fa-toggle-on"></i> Ja</button>
    <button v-if="data == 2" class="si-btn si-btn-off" >
      <i class="fa fas fa-toggle-off"></i> Nein</button>
      
  </span>
  <span v-else-if="data">
    {{ data }}
  </span>
  <span v-else><i class="fa fa-times"></i></span>

</template>

<script>

export default {
  components: {

  },
  data() {
    return {

    };
  },
  props: {
    data: Object,
    typ: String
  },
  created: function () {
  },
  methods: {
   
  }
};
</script>