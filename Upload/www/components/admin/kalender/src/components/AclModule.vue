<template>
  <div id="app padding-t-m">

    <div class="text-big-2">Access Control List</div>

    <div class="acl">

      <ul class="flex-row noListStyle">
        <li class="flex-1">
          <h5 class="text-bold">Schüler</h5>
          <ul class="noListStyle">
            <li>
              <input type="checkbox" v-model="acl.groups.schueler.read" true-value="1" false-value="0" />
              Lesen
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.schueler.write" true-value="1" false-value="0" />
              Schreiben
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.schueler.delete" true-value="1" false-value="0" />
              Löschen
            </li>
          </ul>
        </li>
        <li class="flex-1">
          <h5 class="text-bold">Eltern</h5>
          <ul class="noListStyle">
            <li>
              <input type="checkbox" v-model="acl.groups.eltern.read" true-value="1" false-value="0" />
              Lesen
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.eltern.write" true-value="1" false-value="0" />
              Schreiben
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.eltern.delete" true-value="1" false-value="0" />
              Löschen
            </li>
          </ul>
        </li>
        <li class="flex-1">
          <h5 class="text-bold">Lehrer</h5>
          <ul class="noListStyle">
            <li>
              <input type="checkbox" v-model="acl.groups.lehrer.read" true-value="1" false-value="0" />
              Lesen
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.lehrer.write" true-value="1" false-value="0" />
              Schreiben
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.lehrer.delete" true-value="1" false-value="0" />
              Löschen
            </li>
          </ul>
        </li>
        <li class="flex-1">
          <h5 class="text-bold">Sonstige</h5>
          <ul class="noListStyle">
            <li>
              <input type="checkbox" v-model="acl.groups.none.read" true-value="1" false-value="0" />
              Lesen
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.none.write" true-value="1" false-value="0" />
              Schreiben
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.none.delete" true-value="1" false-value="0" />
              Löschen
            </li>
          </ul>
        </li>
        <li class="flex-1">
          <h5 class="text-bold">Eigentümer</h5>
          <ul class="noListStyle">
            <li>
              <input type="checkbox" v-model="acl.groups.owne.read" true-value="1" false-value="0" />
              Lesen
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.owne.write" true-value="1" false-value="0" />
              Schreiben
            </li>
            <li>
              <input type="checkbox" v-model="acl.groups.owne.delete" true-value="1" false-value="0" />
              Löschen
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
