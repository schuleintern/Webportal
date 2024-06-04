<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">

        <div class="avatar">
          <img :src="data.avatar" alt="" title=""/>
        </div>

        <div class="vorname">{{data.vorname}}</div>
        <div class="nachname">{{data.nachname}}</div>

        <div v-if="data.type=='isTeacher'" class="type isTeacher">- Lehrer -</div>
        <div v-if="data.type=='isPupil'" class="type isPupil">
          <div class="klasse"><label>Klasse:</label> {{data.klasse}}</div>
        </div>

        <div class="toolbar">
          <a v-if="data.id" :href="'index.php?page=MessageCompose&recipient=U:'+data.id"  class="si-btn"><i class="fa fa-envelope"></i> Anschreiben</a>
        </div>

      </div>
    </div>

  </div>
</template>

<script>





export default {

  data() {
    return {
      open: false
    };
  },
  props: {
      data: Object
  },
  created: function () {

  },
  mounted() {
    // access our input using template refs, then focus
  },
  watch: {
    data: function(newVal, oldVal) {
      if (newVal == false) {
        this.open = false;
      } else {
        this.open = true;
      }
    }
  },
  methods: {
    handlerClose: function () {
      this.data = false;
      this.$emit('close');
    }
  }


};
</script>

<style>

</style>