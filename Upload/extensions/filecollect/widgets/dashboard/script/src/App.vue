<template>
  <div class="">
    <h4><i class="fa fas fa-file-import margin-r-s"></i> Uploads</h4>
    <div class="padding-l-m">
      <div v-bind:key="index" v-for="(item, index) in  items" class="margin-b-s border-b">
        <div class="text-green">Noch {{ item.endDateNow }}</div>
        <div class="padding-t-s padding-b-s"><i class="fa fas fa-folder margin-r-s"></i> {{ item.c_title }} -
          {{ item.title }}
        </div>
      </div>
    </div>

  </div>
</template>

<script>
const axios = require('axios').default;

export default {
  components: {},
  data() {
    return {

      items: []

    };
  },
  created: function () {

    console.log('ccc');

    this.loading = true;
    var that = this;
    axios.get('rest.php/filecollect/getMyFolders')
        .then(function (response) {
          if (response.data) {
            if (response.data.error) {
              that.error = '' + response.data.msg;
            } else {
              that.items = response.data;
            }
          } else {
            that.error = 'Fehler beim Laden. 01';
          }
        })
        .catch(function () {
          that.error = 'Fehler beim Laden. 02';
        })
        .finally(function () {
          // always executed
          that.loading = false;
        });

  },
  mounted() {


  },
  methods: {}

};
</script>

<style>

</style>