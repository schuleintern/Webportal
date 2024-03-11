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
          <label>Betrag</label>
          {{ form.amount }} EUR
        </li>
        <li>
          <label>Verwendungszweck</label>
          {{ form.orderNr }}
        </li>
      </ul>
      <ul>
        <li>
          <label>Empfaenger</label>
          {{ bankEmpfaenger }}
        </li>
        <li>
          <label>Bankname</label>
          {{ bankName }}
        </li>
        <li>
          <label>IBAN</label>
          {{ bankIBAN }}
        </li>
        <li>
          <label>BIC</label>
          {{ bankBIC }}
        </li>
      </ul>
    </div>


  </div>

</template>

<script>

export default {
  name: 'ItemComponent',
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false,

      bankEmpfaenger: window.globals.bankEmpfaenger,
      bankName: window.globals.bankName,
      bankIBAN: window.globals.bankIBAN,
      bankBIC: window.globals.bankBIC
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