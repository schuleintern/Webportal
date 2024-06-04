<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="flex-1">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
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
          <label>Zahlungsempfänger</label>
          {{ form.payee }}
        </li>
        <li>
          <label>Betrag</label>
          {{ form.amount }} EUR
        </li>
        <li>
          <label>Fälligkeitsdatum</label>
          {{ form.dueDate }}
        </li>
        <li>
          <label>Benutzer*innen</label>
          <div v-bind:key="index" v-for="(item, index) in  form.userlist" class="flex-row margin-b-s" >
            <User :data="item"  class="margin-r-l"></User>
            <button v-if="item.buchung_state == 1" class="si-btn si-btn-off"><i class="fa fa-check"></i> Offen</button>
            <button v-if="item.buchung_state == 2" class="si-btn si-btn-off text-green"><i class="fa fa-check"></i> Bezahlt</button>
          </div>
        </li>
        <li>
          <label>Erstellt:</label>
          <div class="padding-b-s">{{form.createdTime}}</div>
          <User :data="form.createdUser"></User>
        </li>

      </ul>
    </div>

  </div>

</template>

<script>
import User from "@/mixins/User.vue";
export default {
  name: 'ItemComponent',
  components: {User},
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