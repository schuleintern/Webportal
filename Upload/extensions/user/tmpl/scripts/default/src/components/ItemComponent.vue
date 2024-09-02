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
          <span v-if="item.userEMail">{{ item.userEMail }}</span>
          <span v-else class="text-red">Keine E-Mail hinterlegt!</span>
        </li>
        <li v-if="item.user && item.user.klasse">
          <label>Klasse</label>
          <span>{{ item.user.klasse }}</span>
        </li>
        <li v-if="item.user && item.user.geburtstag">
          <label>Geburtstag</label>
          <span>{{ item.user.geburtstag }} - {{ item.user.alter }} Jahre</span>
        </li>
        <li v-if="item.user && item.user.ort">
          <label>Ort</label>
          <span>{{ item.user.ort }}</span>
        </li>
        <li v-if="item.adressen">
          <label>Adressen</label>
          <UserAdresse :data="item.adressen"></UserAdresse>
        </li>
        <li v-if="item.emails">
          <label>E-Mail Adressen</label>
          <div class="margin-t-m">
            <div v-bind:key="it" v-for="(email, it) in  item.emails" class="">
                <a class="si-btn si-btn-border text-black" :href="'mailto: '+email.email">{{ email.email }}</a>
            </div>
          </div>
        </li>

        <li v-if="item.user && item.user.childs">
          <label>Kinder</label>
          <div v-bind:key="it" v-for="(child, it) in  item.user.childs" class="">
            {{child.name}}
          </div>
        </li>

      </ul>
      <ul class="flex-2">
        <li v-if="item.user">
          <label>Profilbild</label>
          <User :data="item.user" :size="'avatar'" class="width-15rem"></User>
        </li>
        <li v-if="item.user && item.user.bekenntnis">
          <label>Bekenntnis</label>
          <span>{{ item.user.bekenntnis }}</span>
        </li>
        <li v-if="item.user && item.user.ausbildungsrichtung">
          <label>Ausbildungsrichtung</label>
          <span>{{ item.user.ausbildungsrichtung }}</span>
        </li>
        <li v-if="item.user">
          <label>Type</label>
          <span>{{ item.user.typeText }}</span>
        </li>
        <li class="text-small text-grey">
          <label>System User ID</label>
          <span>{{ item.userID }}</span>
        </li>
      </ul>
    </div>


  </div>
</template>

<script>

import User from '../mixins/User.vue'
import UserAdresse from "@/mixins/UserAdresse.vue";

export default {
  name: 'ItemComponent',
  components: {
    UserAdresse,
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