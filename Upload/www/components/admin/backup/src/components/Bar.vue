<template>
  <div class="bar flex-2">
    Bar:
    <ul>
      <li>
        <button class="btn-primary"
          @click="clickHandler()" >
          Antworten
        </button>
      </li>
      <li>
        <button class="btn-primary"
          @click="clickHandler()" >
          Allen Antworten
        </button>
      </li>
      <li>
        <button class="btn-primary"
          @click="clickHandler()" >
          Weiterleiten
        </button>
      </li>
      <li>
        <button class="btn-primary"
          @click="clickHandler()" >
          Verschieben
        </button>
      </li>
      <li>
        <button class="btn-primary"
          @click="clickHandler()" >
          Archivieren
        </button>
      </li>
      <li>
        <button class="btn-primary"
          @click="clickHandler()" >
          Drucken (PDF)
        </button>
      </li>
      <li>
        <button class="btn-primary"
          @click="clickHandler()" >
          Löschen
        </button>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'Bar',
  props: {
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
