<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="flex-1">
      </div>
      <div class="flex-1 flex-row flex-end">
      </div>
    </div>


    <div class="si-details flex-row">
      <ul class="flex-3">
        <li>
          <label>Benutzername</label>
          <span>{{ item.userName }}</span>
        </li>
        <li>
          <label>Name</label>
          <span v-if="item.user">{{ item.user.name }}</span>
        </li>
        <li>
          <label>E-Mail</label>
          <span>{{ item.userEMail }}</span>
        </li>
      </ul>
      <ul class="flex-2">
        <li v-if="item.user">
          <label>Profilbild</label>

          <User :data="item.user" :size="'avatar'" class="width-15rem"></User>
        
        </li>
      </ul>
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

      required: '',
      deleteBtn: false,
      selfURL: window.globals.selfURL,
      url_root: window.globals.url_root
    };
  },
  props: {
    acl: Array,
    item: Array
  },
  created: function () {

  },
  methods: {

    /*
    handlerForm: function () {
      this.$bus.$emit('page--open', {
        page: 'form'
      });
    },
    */


  }


};
</script>

<style></style>