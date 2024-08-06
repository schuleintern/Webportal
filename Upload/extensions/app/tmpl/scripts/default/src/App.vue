<template>

  <div>

    <div class="si-details" v-if="appData.active == true">
      <h2 class="margin-l-l"><i class="fas fa-mobile-alt"></i> App - {{ appData.appTitle }}</h2>
      <div class="flex-row">
        <div class="flex-5">
          <ul>
            <li style="padding-left: 2.3rem" >
              <h3>Login Daten:</h3>
            </li>
            <li>
              <label>Schulnummer</label>
              {{schulnummer}}
            </li>
            <li>
              <label>Benutzername</label>
              {{username}}
            </li>
          </ul>
        </div>
        <div class="flex-2">
          <ul>
            <li>
              <label>Qr-Code für den Login der Schule-Intern App</label>
              <vue-qrcode :value="qr_str" :options="{ width: 350 }"></vue-qrcode>
            </li>
          </ul>
        </div>
      </div>


    </div>



  </div>
</template>

<script>



export default {

  name: 'App',
  components: {
  },
  data() {
    return {
      appData: window.globals.app,
      schulnummer: window.globals.schulnummer,
      username: window.globals.username,
      qr_str: window.globals.qr_str,

      apiURL: window.globals.apiURL,
      acl: window.globals.acl,


    };
  },
  created() {


  },
  methods: {

  }
}
</script>

<style>

</style>
