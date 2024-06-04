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
        <li>
          <label>Profilbild</label>

          <User v-if="item.user" :data="item.user" :size="'avatar'" class="width-15rem"></User>

        
        </li>
      </ul>
    </div>

    <div class="si-form ">
      <ul>
        <li>
          <h3>Nachrichten</h3>
        </li>
        <li>
          <label>Signatur</label>
          <textarea v-model="item.userSignature"></textarea>
        </li>
        <li>
          <label>Abwesenheit</label>
          <span>{{ item.userAutoresponse }}</span>

          <FormToggle :disable="1" :input="item.userAutoresponse" @change="handlerToggle($event)">
          </FormToggle>
        </li>
        <li v-if="item.userAutoresponse">
          <label>Abwesenheitsnotiz</label>
          <span>{{ item.userAutoresponseText }}</span>
        </li>

      </ul>

    </div>


  </div>
</template>

<script>
import FormToggle from '../mixins/FormToggle.vue'
import User from '../mixins/User.vue'

export default {
  name: 'ItemComponent',
  components: {
    FormToggle, User
  },
  data() {
    return {

      required: '',
      deleteBtn: false,
      selfURL: window.globals.selfURL
    };
  },
  props: {
    acl: Array,
    item: Array
  },
  created: function () {

  },
  methods: {


    handlerToggle: function ($e, data) {

      this.item.userAutoresponse = $e.value;
    }
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