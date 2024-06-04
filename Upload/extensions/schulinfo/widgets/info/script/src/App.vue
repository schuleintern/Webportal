<template>
  <div class="">

    <a class="si-btn si-btn-border" href="index.php?page=ext_schulinfo">
      <i class="fa fa-info-circle"></i> {{info.schulinfo_name}}</a>

    <div class="margin-t-m">
      <span class="text-small" v-if="info.schulinfo_name_zusatz">{{info.schulinfo_name_zusatz}}</span><br/>
      <span v-if="info.schulinfo_adresse1">{{info.schulinfo_adresse1 }}<br/></span>
      <span v-if="info.schulinfo_adresse2">{{info.schulinfo_adresse2 }}<br/></span>
      {{info.schulinfo_plz}} {{info.schulinfo_ort}}<br/><br/>

      <div v-if="info.schulinfo_telefon"><a :href="'tel: '+info.schulinfo_telefon"><i class="fa fa-phone"></i> {{info.schulinfo_telefon}}</a></div>
      <div v-if="info.schulinfo_fax"><a :href="'fax: '+info.schulinfo_fax"><i class="fa fa-print"></i> {{info.schulinfo_fax}}</a></div>
      <div v-if="info.schulinfo_email"><a :href="'mailto: '+info.schulinfo_email"><i class="fa fa-envelope"></i> {{info.schulinfo_email}}</a></div>
      <div v-if="info.schulinfo_homepage"><a :href="'http://'+info.schulinfo_homepage" target="_blank"><i class="fa fa-link"></i> {{ info.schulinfo_homepage }}</a></div>
    </div>

  </div>
</template>

<script>

//const axios = require('axios').default;

export default {
  components: {},
  data() {
    return {

      info: globals_widget_schulinfo_info

    };
  },
  created: function () {

  },
  methods: {}

};
</script>

<style>

</style>