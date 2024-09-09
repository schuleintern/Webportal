<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
        <button class="si-btn " @click="handlerSaveForm()"><i class="fa fa fa-save"></i> Speichern</button>
      </div>
      <div class="">

        <button class="si-btn si-btn-light" @click="handlerDelete()"><i
            class="fa fa fa-trash"></i> Löschen
        </button>
      </div>
    </div>


    <form class="si-form flex-row" >
      <div class="flex-5">
        <ul>
          <li style="display: block" class="">
            <button v-if="form.ferien" class="si-btn si-btn-off  margin-l-l"><i class="fa fas fa-spa"></i> Ferien
            </button>
          </li>
          <li :class="required">
            <label>Title</label>
            <input type="text" v-model="form.title">
          </li>
          <li class="">
            <label>Farbe</label>
            <label class="small">im hex Format (z.b. #ffcc22 )</label>
            <input type="text" v-model="form.color" class="width-30rem">
          </li>
          <li>
            <FormAcl v-if="form && acl" :form="formACL" :acl="form.acl" @change="handlerChangeAcl"></FormAcl>
          </li>


        </ul>
      </div>
      <div class="flex-3">
        <ul>
          <li class="">
            <label>Veröffentlicht</label>
            <FormToggle :input="form.state" class="margin-l-l"
                        @change="handlerToggleChange($event, form,'state')"></FormToggle>
          </li>

          <li>
            <label>Kalenderadmin</label>
            <UserSelect :preselected="form.admins" @submit="handlerChangeUserlist"></UserSelect>
            <span v-bind:key="index" v-for="(item, index) in  form.admins" class="margin-t-s">
              <User :data="item"></User>
            </span>
          </li>
        </ul>
      </div>


    </form>

  </div>

</template>

<script>

import FormToggle from './../mixins/FormToggle.vue';
import FormAcl from './../mixins/FormAcl.vue';
import User from './../mixins/User.vue';
import UserSelect from './../mixins/UserSelect.vue';


export default {
  name: 'ItemComponent',
  components: {
    FormToggle, FormAcl,
    UserSelect, User
  },
  data() {
    return {
      colors: '#333',

      form: {},
      required: '',
      formACL: {
        'acl': {
          'schueler': {read: "1", write: "1", delete: "1"},
          'eltern': {read: "1", write: "1", delete: "1"},
          'lehrer': {read: "1", write: "1", delete: "1"},
          'none': {read: "1", write: "1", delete: "1"}
        }
      }


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

    handlerChangeUserlist: function (data) {
      this.form.admins = data;
      //this.$emit('submit', data);
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
        callback: function () {
          //that.item.id = data.id;
          that.handlerBack();
        }
      });
      return false;
    },

    handlerDelete() {
      if (confirm('Wirklich löschen?')) {
        this.$bus.$emit('item--delete', {
          item: this.form
        });
      }
    },


    handlerToggleChange(event, item, elm) {
      item[elm] = event.value;
      //this.handlerSaveForm();
    },

    handlerChangeAcl() {

      //this.form.acl = JSON.stringify(newVal)
      /*
      this.$bus.$emit('item--acl', {
        acl: newVal,
        id: this.item.id
      });
      */

    }

  }


};
</script>

<style>

</style>