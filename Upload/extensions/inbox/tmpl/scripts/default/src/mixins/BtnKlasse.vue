<template>

  <div class="si-btn-multiple">
    <button v-if="jahrgang(content)" class="si-btn margin-r-s si-btn-border" @click="handlerSelectGroup(typ, content)">{{ jahrgang(content) }}.
      Jahrgangstufe
    </button>

    <button v-bind:key="index" v-for="(item, index) in  content"
            class="si-btn margin-r-s" :class="{'si-btn-active': selectActive(typ, item) }"
            @click="handlerSelect($event, typ, item)">{{ item }}
    </button>

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
  computed: {},
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
      this.$emit('submit', {
        typ: typ,
        content: content
      });
      return false;
    },
    handlerSelect($event, typ, content) {
      $event.preventDefault();
      this.$emit('submit', {
        typ: typ,
        content: content
      });
      return false;
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

}
</script>

<style scoped>

</style>