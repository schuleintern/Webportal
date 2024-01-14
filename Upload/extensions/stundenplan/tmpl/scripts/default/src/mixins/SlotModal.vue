<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content text-center ">

        <div class="si-details">
          <ul class="" >
            <li class="text-bold">
              <label>Fach</label>
              <div v-if="toggleVplan && vplan && vplan.fach_neu && vplan.fach_neu != data.subject" class="text-red margin-r-s">{{ vplan.fach_neu }}</div>
              
              <button v-if="acl.write == 1" class="si-btn si-btn-border" v-on:click="handlerOpenPlan(data.subject, 'subject')"><i class="fa fa-flask"></i> {{ data.subject }}</button>
              <div v-else>{{ data.subject }}</div>
            </li>
            <li class=" ">
              <label>Klasse</label>
              <div v-if="toggleVplan && vplan && vplan.klasse && vplan.klasse != data.grade" class="text-red margin-r-s">{{ vplan.klasse }}</div>
              <button v-if="acl.write == 1" class="si-btn si-btn-border" v-on:click="handlerOpenPlan(data.grade, 'grade')"><i class="fa fa-users"></i> {{ data.grade }}</button>
              <div v-else>{{ data.grade }}</div>
            </li>
            <li class=" ">
              <label>Lehrerin</label>
              <div v-if="toggleVplan && vplan && vplan.user_neu && vplan.user_neu != data.teacher" class="text-red margin-r-s">{{ vplan.user_neu }}</div>
              <button v-if="acl.write == 1" class="si-btn si-btn-border" v-on:click="handlerOpenPlan(data.teacher, 'teacher')"><i class="fa fa-user"></i> {{ data.teacher }}</button>
              <div v-else>{{ data.teacher }}</div>
            </li>
            <li class="">
              <label>Raum</label>
              <div v-if="toggleVplan && vplan && vplan.raum && vplan.raum != data.room" class="text-red margin-r-s">{{ vplan.raum }}</div>
              <button v-if="acl.write == 1" class="si-btn si-btn-border" v-on:click="handlerOpenPlan(data.room, 'room')"><i class="fa fa-door-open"></i> {{ data.room }}</button>
              <div v-else>{{ data.room }}</div>
            </li>
          </ul>
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
      show: Boolean,
      data: Object,
      vplan: Array,
      toggleVplan: Number,
    acl: Array
  },
  created: function () {

  },
  mounted() {
    // access our input using template refs, then focus
  },
  watch: {
    show: function(newVal) {
      if (newVal == false) {
        this.open = false;
      } else {
        this.open = true;
      }
    }
  },
  methods: {
    handlerClose: function () {
      this.open = false;
      this.$emit('close');
    },

    handlerOpenPlan(content, key) {

      if (content && key) {

        var that = this;
        
        this.$bus.$emit('item--load', {
          item: [content, key],
          callback: function () {
            //console.log('done');
            that.open = false;
          }
        });
      }


    }

  }


};
</script>

<style>

</style>