<template>
  <div>


    <div class="margin-b-l">
      <h3><i class="fa fas fa-users-cog margin-r-m"></i>Hauptadministratoren</h3>
      <div class="padding-l-l padding-t-m">
        <ul class="noListStyle">
          <li v-bind:key="index" v-for="(item, index) in adminList"
              class="padding-b-s padding-l-l">
            <User :data="item"></User>
          </li>
        </ul>
      </div>
    </div>


    <div class="padding-t-s padding-b-l">

      <h3><i class="fa fas fa-user-cog margin-r-m"></i>Moduladministratoren</h3>
      <div class="padding-l-l">
        <ul v-if="adminExtension.length" class="noListStyle">
          <li v-bind:key="index" v-for="(item, index) in adminExtension"
              class="padding-b-s padding-l-l">
            <User :data="item"></User>
          </li>
        </ul>
        <div v-else class="padding-l-l text-grey"><i class="fas fa-ban margin-r-m"></i>Bisher keine</div>

        <UserSelect class="padding-l-l" :preselected="adminExtension" @submit="handlerChangeUserlist"></UserSelect>
      </div>

    </div>


  </div>
</template>

<script>

import User from './User.vue';
import UserSelect from './UserSelect.vue';

export default {
  components: {
    User, UserSelect
  },
  data() {
    return {

    };
  },
  props: {
    adminList: Array,
    adminExtension: Array
  },
  created: function () {

  },
  methods: {

    handlerChangeUserlist: function (data) {
      this.$emit('submit', data);
    }
  }


};
</script>

<style>

</style>