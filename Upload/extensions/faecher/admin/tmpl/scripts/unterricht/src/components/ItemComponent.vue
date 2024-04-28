<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
        </button>
      </div>
    </div>

    <div class="width-70vw">
      <div class="si-form ">
        <ul class="">
          <li>
            <User v-if="form.id" :data="form"></User>
          </li>
          <li>
            <label>ID</label>
            <input type="text" v-model="form.id" readonly>
          </li>
          <li>
            <label>Vorname</label>
            <input type="text" v-model="form.vorname" readonly>
          </li>
          <li>
            <label>Nachname</label>
            <input type="text" v-model="form.nachname" readonly>
          </li>
          <li>
            <label>Type</label>
            <input type="text" v-model="form.type" readonly>
          </li>
          <td><FormToggle :disable="false" :input="item.isKlassenunterricht" labelTrue="Ja" labelFalse="Nein"></FormToggle></td>



        </ul>

      </div>
    </div>

  </div>

</template>

<script>

import User from '../mixins/User.vue'
import FormToggle from "@/mixins/FormToggle.vue";

export default {
  name: 'ItemComponent',
  components: {
    FormToggle,
   User
  },
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false
    };
  },
  props: {
    acl: Array,
    item: []
  },
  created: function () {
    this.form = this.item;
  },
  methods: {

    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },


  }


};
</script>

<style>

</style>