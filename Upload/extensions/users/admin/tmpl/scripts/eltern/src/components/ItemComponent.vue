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
          <li v-if="form.elternUserID">
            <User v-if="form.elternUser" :data="form.elternUser"></User>
          </li>
          <li v-if="form.elternUserID">
            <label>User ID</label>
            <input type="text" v-model="form.elternUserID" readonly>
          </li>
          <li v-if="form.elternUserID">
            <label>Benutzername</label>
            <input type="text" v-model="form.elternUserName" readonly>
          </li>
          <li v-if="form.elternUserID || form.elternUserID === 0" class="flex-row">
            <button class="si-btn" @click="handlerResetUser"><i class="fa fa-sync"></i> Benutzer zurücksetzen</button>
          </li>
          <li v-else>
            Kein Benutzer angelegt!
          </li>
          <li>
            <label>E-Mail</label>
            <input type="text" v-model="form.elternEMail" readonly>
          </li>
          <li>
            <label>ASV ID</label>
            <input type="text" v-model="form.elternSchuelerAsvID" readonly>
          </li>
          <li>
            <label>Schüler*in</label>
            <input type="text" v-model="form.schuelerUserName" readonly>
            <User v-if="form.schuelerUser" :data="form.schuelerUser" class="margin-t-m"></User>
          </li>


        </ul>

      </div>
    </div>

  </div>

</template>

<script>

import User from '../mixins/User.vue'

export default {
  name: 'ItemComponent',
  components: {
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
    acl: Object,
    item: []
  },
  created: function () {
    this.form = this.item;
  },
  methods: {

    handlerResetUser: function () {
      this.$bus.$emit('item--reset', {
        data: this.form
      });
    },
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