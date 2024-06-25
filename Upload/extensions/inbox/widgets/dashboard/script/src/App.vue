<template>

  <div>

    <div class="flex-row">
      <h4 class="flex-1"><i class="fas fa fa-envelope"></i> Nachrichten</h4>
      <div>
        <a class="si-btn si-btn-border si-btn-icon si-btn-small" href="index.php?page=ext_inbox"><i class="fas fa-external-link-alt"></i></a>
      </div>
    </div>
    <ul v-if="list && list.length >= 1" class="noListStyle">
      <li v-bind:key="index" v-for="(item, index) in  list" class="line-oddEven padding-m">

        <h4 class="title blockInline"><i class="fas fa fa-envelope-open"></i> {{ item.title }} </h4>
        <span class=" margin-l-m label bg-red" v-if="item.data && item.data.length >= 1">{{item.data.length}}</span>

        <table class="si-table si-table-style-allLeft">
        <tbody v-if="item.data && item.data.length >= 1" class="noListStyle">
          <tr v-bind:key="i" v-for="(message, i) in  item.data" class="line-oddEven padding-m">

            <td class="">{{ message.date }}</td>
            <td class="">{{ message.from.title }}</td>
            <td class=""><a :href="'index.php?page=ext_inbox&iid='+message.inbox_id+'&mid='+message.id">{{ message.subject }}</a></td>

          </tr>
        </tbody>
        </table>

      </li>
    </ul>
    <div v-else>
      <div class="padding-m"><i>- Keine neuen Nachrichten -</i></div>
    </div>


  </div>
</template>

<script>

const axios = require('axios').default;

export default {

  name: 'App',
  components: {},
  data() {
    return {

      apiURL: 'rest.php/inbox',
      list: []

    };
  },
  created() {
    this.loadMessages();
    //this.list = window._widget_kalender_events.today;
    //console.log(this.list)
  },
  methods: {

    loadMessages() {


      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getMyMessages').then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            that.list = response.data;
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      }).catch(function () {
        that.error = 'Fehler beim Laden. 02';
      }).finally(function () {
        // always executed
        that.loading = false;
      });
    },

  }
}
</script>

<style>

</style>
