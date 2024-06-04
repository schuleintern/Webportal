<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="flex-1">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
        </button>
      </div>
      <div class="flex-1 flex-row flex-end">
      </div>
    </div>


    <div class="si-details">
      <ul>
        <li>
          <h3>{{ form.title }}</h3>
        </li>
        <li>
          <label>Benutzer*in</label>
          <User :data="form.user"></User>
        </li>
        <li>
          <label>Betrag</label>
          {{ form.amount }} EUR
        </li>
        <li>
          <label>Verwendungszweck</label>
          {{ form.orderNr }}
        </li>
        <li>
          <button v-if="item.state == 1" @click="handlerState" class="si-btn"><i class="fa fa-plus"></i>Wurde Bezahlt
          </button>
        </li>
      </ul>
    </div>


  </div>

</template>

<script>

import User from "@/mixins/User.vue";

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
    handlerState: function () {
      this.$bus.$emit('item--state', this.form);
    },


  }


};
</script>

<style>

</style>