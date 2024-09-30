<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="flex-1">
        <button class="si-btn si-btn-light margin-r-s" @click="handlerBack()"><i class="fa  fa-angle-left"></i> Zurück</button>
        <button class="si-btn" @click="handlerSaveForm()"><i class="fa  fa-paper-plane"></i> Krankmeldung abgeben</button>
      </div>
      <!--
      <button class="si-btn si-btn-light" @click="handlerDelete()" v-if="deleteBtn === false"><i class="fa fa fa-trash"></i> Löschen</button>
      <button class="si-btn si-btn-red" @click="handlerDeleteDo(item)" v-if="deleteBtn === true"><i class="fa fa fa-trash"></i> Wirklich Löschen ?</button>
      -->
      <div class="si-hinweis" v-if="hinweisForm" v-html="hinweisForm"></div>
    </div>
    <div class="si-form">
      <ul>

        <li :class="required">
          <label>Benutzer*in:</label>
          <select required v-model="form.user" v-if="listDateStart">
            <option v-bind:key="index" v-for="(item, index) in  listUsers" :value="item.id"  >{{ item.name }} -
              {{ item.klasse }}
            </option>
          </select>
        </li>
        <li :class="required">
          <label>Krankmelden ab:</label>
          <FormMulti :input="form.dateStart" :options="listDateStart" @submit="handlerDateStart"></FormMulti>
        </li>

        <li :class="required">
          <label>Dauer der Krankmeldung:</label>
          <FormMulti :input="form.dateAdd" :options="listDateAdd" @submit="handlerDateAdd"></FormMulti>
        </li>

        <li v-if="!hinweisStatus">
          <label>Bermerkung</label>
          <textarea v-model="form.info" maxlength="300"></textarea>
          <div class="si-hinweis" v-if="hinweisFormBemerkung" v-html="hinweisFormBemerkung"></div>
        </li>


      </ul>

    </div>

  </div>

</template>

<script>
import FormMulti from './../mixins/FormMulti.vue'

export default {
  name: 'ItemComponent',
  components: {
    FormMulti
  },
  data() {
    return {
      listUsers: window.globals.listUsers || [],
      listDateStart: window.globals.listDateStart || [],
      listDateAdd: window.globals.listDateAdd || [],
      hinweisForm: window.globals.hinweisForm || false,
      hinweisFormBemerkung: window.globals.hinweisFormBemerkung || false,
      hinweisStatus: window.globals.hinweisStatus,

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


    if (this.listUsers.length == 1) {
      this.form.user = this.listUsers[0].id;
    }


  },
  methods: {

    handlerDateStart: function (val) {
      this.form.dateStart = val.value;
    },
    handlerDateAdd: function (val) {
      this.form.dateAdd = val.value;
    },

    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },

    handlerSaveForm() {

      if (!this.form.user || !this.form.dateStart || !this.form.dateAdd ) {
        this.required = 'required';
        return false;
      }

      //var that = this;
      this.$bus.$emit('item--submit', {
        item: this.form,
        callback: function () {
          //that.item.id = data.id;
        }
      });
      return false;
    },

    handlerDelete() {
      this.deleteBtn = true;
    },

    handlerDeleteDo(item) {

      this.$bus.$emit('item--delete', {
        item: item
      });

    }

  }


};
</script>

<style>

</style>