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
            <label>Neues Elternteil für Schüler*in:</label>
            <User v-if="form.schueler.id" :data="form.schueler"></User>
            <UserSelect @submit="handelerUser"  :preselected="[]" max-anzahl="1" prefilter="isPupil" ></UserSelect>
          </li>
          <li>
            <button class="si-btn" @click="handlerSubmit"><i class="fa fa-plus"></i> Hinzufügen</button>
          </li>

        </ul>

      </div>
    </div>

  </div>

</template>

<script>

import User from '../mixins/User.vue'
import UserSelect from '../mixins/UserSelect.vue'

export default {
  name: 'ItemComponent',
  components: {
    UserSelect, User
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
    if (!this.form.schueler) {
      this.form.schueler = false;
    }
  },
  methods: {

    handelerUser: function (val) {
      if (val[0] && val[0].id) {
        this.form.schueler = val[0];
      }
    },
    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },
    handlerSubmit: function () {
      this.$bus.$emit('item--submit', this.form);
    }


  }


};
</script>

<style>

</style>