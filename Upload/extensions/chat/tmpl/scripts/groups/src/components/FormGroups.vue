<template>
  <div class="si-form" >

    <div class="header">
      <button class="si-btn si-btn-light" v-on:click="handlerBack">Zurück</button>
      <div class="title">Gruppe</div>
    </div>
    <div class="">
      <ul>
        <li>
          <label>Name</label>
          <input type="text" v-model="formData.title">
        </li>
        <li>
          <label>Mitglieder</label>

          <ul>
            <li v-bind:key="index" v-for="(item, index) in  formData.members" class="user">
              <User v-if="item" v-bind:data="item"></User>
              <button class="si-btn si-btn-light remove margin-l-m" v-on:click="handlerUserRemove(item)"><i class="fas fa-trash"></i></button>
            </li>
            <li>
              <UserSelect @submit="handlerSubmitUser"></UserSelect>
            </li>
          </ul>

        </li>
        <li>
          <button class="si-btn" v-on:click="handlerSubmit">Speichern</button>
        </li>
      </ul>

    </div>

  </div>
</template>

<script>

import User from '../mixins/User.vue'
import UserSelect from '../mixins/UserSelect.vue'


export default {
  components: {
    User,
    UserSelect
  },
  data() {
    return {

    };
  },
  props: {
    formData: Object
  },
  created: function () {

  },
  methods: {

    handlerUserRemove: function (user) {
      var that = this;
      this.formData.members.forEach(function (o, i) {
        if (o.id == user.id) {
          that.formData.members.splice(i, 1);
        }
      });
    },
    handlerSubmitUser: function (userlist) {
      this.formData.members = [...this.formData.members, ...userlist];
    },
    handlerBack: function () {

      this.$emit('close')
    },
    handlerSubmit: function () {

      this.$emit('formSubmitGroup', this.formData)
    }

  }

};
</script>

<style scoped>

.user {
  flex-direction: row;
}
.user .remove {
  display: inline-block;
}
</style>