<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
        </button>
        <button class="si-btn" v-if="changed" v-on:click="handlerSubmit"><i class="fas fa-save"></i> Speichern</button>
      </div>
      <div>
        <button v-if="form.id && form.type == 'isNone' && acl.delete" class="si-btn si-btn-red" v-on:click="handlerDelete"><i class="fas fa-trash"></i> Löschen</button>
      </div>
    </div>

    <div class="width-70vw">
      <div class="si-form flex-row">
        <div class="flex-3">
          <ul class="">

            <li>
              <label>Vorname</label>
              <input v-if="form.type != 'isNone'" type="text" v-model="form.vorname" readonly>
              <input v-else type="text" v-model="form.vorname" @change="changed = true">
            </li>
            <li>
              <label>Nachname</label>
              <input v-if="form.type != 'isNone'" type="text" v-model="form.nachname" readonly>
              <input v-else type="text" v-model="form.nachname" @change="changed = true">
            </li>
            <li>
              <label>Benutzername</label>
              <input v-if="form.type != 'isNone'" type="text" v-model="form.username" readonly>
              <input v-else type="text" v-model="form.username" @change="changed = true">
            </li>
            <li>
              <label>Passwort</label>
              <input type="text" v-model="form.password" @change="changed = true" >
              <div class="text-small padding-l-l">Feld Leer Lassen falls nicht geändert werden soll.</div>
            </li>
          </ul>
        </div>
        <div class="flex-1">
          <ul class="">
            <li>
              <User v-if="form.id" :data="form"></User>
            </li>
            <li>
              <label>ID</label>
              <input type="text" v-model="form.id" readonly>
            </li>
            <li>
              <label>Type</label>
              <input type="text" v-model="form.type" readonly>
            </li>
          </ul>
        </div>


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
      deleteBtn: false,
      changed: false
    };
  },
  props: {
    acl: Array,
    item: []
  },
  created: function () {
    this.form = this.item;
    if (!this.form.id || this.form.id == 0) {
      this.form.type = 'isNone';
    }
  },
  methods: {

    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },
    handlerSubmit: function () {

      if (!this.form.username) {
        return false;
      }
      this.deleteItem = false;
      this.$bus.$emit('item--submit', {
        item: this.form
      });


    },
    handlerDelete: function () {
      if (!this.form.id) {
        return false;
      }
      if (confirm('Wirklich löschen?')) {
        this.$bus.$emit('item--delete', {
          item: this.form
        });
      }

    }


  }


};
</script>

<style>

</style>