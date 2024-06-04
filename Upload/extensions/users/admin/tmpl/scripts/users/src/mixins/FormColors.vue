<template>
  <div class="flex-row flex-center-horz">
    <div v-if="value" class="flex-1 margin-r-l" :style="'max-width: 3rem;height: 3rem;border-radius:3rem;background-color:'+value"></div>
    <select v-model="value" v-on:change="handlerToggle" >
      <option v-bind:key="index" v-for="(item, index) in options" v-bind:value="index" >{{item}}</option>
    </select>
  </div>
</template>

<script>

export default {
  data() {
    return {
      value: 0,
      options: {
        "#FF0000": "Rot",
        "#00FF00": "Grün",
        "#0000FF": "Blau",
        "#FFFF00": "Gelb",
        "#FF00FF": "Magenta",
        "#00FFFF": "Cyan",
        "#800080": "Lila",
        "#008000": "Dunkelgrün",
        "#800000": "Dunkelrot",
        "#008080": "Dunkelcyan",
        "#000080": "Dunkelblau",
        "#808080": "Grau",
        "#C0C0C0": "Silber",
        "#808000": "Oliv",
        "#FFA500": "Orange",
        "#A52A2A": "Braun",
        "#9932CC": "Dunkellila",
        "#DC143C": "Karmesinrot",
        "#00CED1": "Dunkeltürkis",
        "#8B008B": "Dunkelmagenta",
        "#32CD32": "Limegrün",
        "#FF6347": "Tomatenrot",
        "#FF4500": "Orangerot",
        "#2E8B57": "Seegrün",
        "#6B8E23": "Olivedrab",
        "#4682B4": "Steelblue",
        "#8A2BE2": "Blaulila",
        "#20B2AA": "Lichtes Türkis",
        "#FFD700": "Goldgelb",
        "#00FA9A": "Mediumspringgreen",
        "#FF8C00": "Dunkelorange",
        "#4B0082": "Indigo",
        "#00FF7F": "Springgreen",
        "#228B22": "Forestgreen",
        "#556B2F": "Dunkelolivgrün",
        "#8B4513": "Saddlebrown",
        "#6A5ACD": "Slateblue",
        "#9400D3": "Dunkelviolet"
      }
    }
  },
  props: {
    input: Text,

    disable: Number
  },
  created: function () {
    this.value = this.input;
  },
  methods: {
    handlerToggle: function () {
      if (this.disable == 0) { // AUS
        return false;
      }
      this.$emit('submit', {
        value: this.value
      });
    }
  }


};
</script>

<style>

</style>