<template>
  <div id="app">

    <h3>Access Control List</h3>

    <div class="acl">

      <ul class="flex-row">
        <li>
          <h5>Schüler</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.schueler.read" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.schueler.write" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.schueler.delete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Eltern</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.eltern.read" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.eltern.write" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.eltern.delete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Lehrer</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.lehrer.read" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.lehrer.write" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.lehrer.delete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Sonstige</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.none.read" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.none.write" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.none.delete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
        <li>
          <h5>Eigentümer</h5>
          <ul>
            <li>
              <input type="checkbox" v-model="acl.groups.owne.read" true-value="1" false-value="0" />
              <label>Lesen</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.owne.write" true-value="1" false-value="0" />
              <label>Schreiben</label>
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.owne.delete" true-value="1" false-value="0" />
              <label>Löschen</label>
            </li>
          </ul>
        </li>
      </ul>

    </div>

  </div>
</template>

<script>

export default {
  name: 'app',
  props: {
    acl: Object
  },
  components: {
    
  },
  data: function () {
    return {
    }
  },
  created: function () {



  },
  methods: {


  }
}
</script>

<style>
</style>
