<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
        </button>
        <button class="si-btn " @click="handlerSaveForm()"><i class="fa fa fa-save"></i> Speichern</button>
      </div>
      <div class="">
        <button class="si-btn si-btn-light" @click="handlerDelete()" v-if="deleteBtn === false"><i
            class="fa fa fa-trash"></i> Löschen
        </button>
        <button class="si-btn si-btn-red" @click="handlerDeleteDo(item)" v-if="deleteBtn === true"><i
            class="fa fa fa-trash"></i> Wirklich Löschen ?
        </button>
      </div>
    </div>

    <div class="width-70vw">
      <div class="si-form ">
        <ul class="">
          <li :class="required">
            <label>Title</label>
            <input type="text" v-model="form.title">
          </li>
          <li class="">
            <label>Leitung</label>
            <div class="flex-row">
              <User v-if="form.leader && form.leader.id" :data="form.leader" class="margin-r-l"></User>
              <UserSelect @submit="handlerChangeUserlist" :preselected="[form.leader]" max-anzahl="1"></UserSelect>
            </div>
          </li>
          <li class="">
            <label>Raum</label>
            <input type="text" v-model="form.room">
          </li>
          <li class="">
            <label>Farbe</label>
            <FormColors :input="form.color" @submit="handlerChangeColor"></FormColors>
          </li>
          <li class="">
            <label>Info</label>
            <input type="text" v-model="form.info">
          </li>
          <li class="">
            <label>Tage</label>
            <FormDays :form="form.days"></FormDays>
          </li>


        </ul>

      </div>
    </div>

  </div>

</template>

<script>
import FormDays from '../mixins/FormDays.vue'
import FormColors from '../mixins/FormColors.vue'
import UserSelect from '../mixins/UserSelect.vue'
import User from '../mixins/User.vue'

export default {
  name: 'ItemComponent',
  components: {
    FormDays, FormColors, UserSelect, User
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
    if (!this.form.days) {
      this.form.days = {};
    }
    if (!this.form.days) {
      this.form.days = {};
    }
    if (!this.form.leader) {
      this.form.leader = false;
    }
  },
  methods: {

    handlerChangeColor: function (data) {

      if (data && data.value) {
        this.form.color = data.value;
      }

    },
    handlerChangeUserlist: function (data) {

      //console.log(data);
      if (data && data[0] && data[0].id) {
        this.form.leader_id = data[0].id;
        this.form.leader = data[0];
      }


    },
    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },

    handlerSaveForm() {

      if (!this.item.title) {
        this.required = 'required';
        return false;
      }

      var that = this;
      this.$bus.$emit('item--submit', {
        item: this.form,
        callback: function (data) {
          that.item.id = data.id;
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