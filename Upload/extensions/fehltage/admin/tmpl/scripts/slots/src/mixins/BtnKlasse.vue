<template>

  <div>
    <button v-if="jahrgang(content)" class="si-btn" @click="handlerSelectGroup(typ, content)">{{jahrgang(content)}}. Jahrgangstufe</button>
    <div>
      <button v-bind:key="index" v-for="(item, index) in  content"
              class="si-btn margin-r-s" :class="{'si-btn-active': selectActive(typ, item) }"
              @click="handlerSelect(typ, item)">{{item}}
      </button>
    </div>
  </div>

</template>

<script>


export default {
  components: {},
  data() {
    return {};
  },
  props: {
    typ: String,
    content: Array,
    selected: Object
  },
  computed: {

  },
  mounted: function () {

  },
  created: function () {

  },
  methods: {
    jahrgang(content) {
      if (content[0]) {
        let ret = parseInt(content[0]);
        if (ret) {
          return ret;
        }
      }
      return false;
    },
    handlerSelectGroup(typ, content) {
      this.$bus.$emit('handlerSelectGroup', {
        typ: typ,
        content: content
      });
    },
    handlerSelect(typ, content) {
      //console.log(typ, content);
      this.$bus.$emit('handlerSelect', {
        typ: typ,
        content: content
      });
    },
    selectActive(typ, content) {
      let ret = false;
      this.selected.forEach((o) => {
        if (o.typ == typ && o.content == content) {
          ret = true;
        }
      })
      return ret;
    }
  }

};
</script>

<style scoped>

</style>