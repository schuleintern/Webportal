<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <button class="si-btn si-btn-light" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
      <button class="si-btn si-btn-light" @click="handlerDelete()" v-if="deleteBtn === false"><i
          class="fa fa fa-trash"></i> Löschen
      </button>
      <button class="si-btn si-btn-red" @click="handlerDeleteDo(item)" v-if="deleteBtn === true"><i
          class="fa fa fa-trash"></i> Wirklich Löschen ?
      </button>
    </div>


    <form class="si-form flex-row" @change="handlerSaveForm($event)">
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

          <li class="">
            <label>Vorausgewählt im Kalender</label>
            <FormToggle :input="form.preSelect" class="margin-l-l"
                        @change="handlerToggleChange($event, form,'preSelect')"></FormToggle>
          </li>
          <li class="">
            <label>Veröffentlichen im allgemeinen ICS Feed</label>
            <FormToggle :input="form.public" class="margin-l-l"
                        @change="handlerToggleChange($event, form,'public')"></FormToggle>
          </li>
        </ul>
      </div>


    </form>

  </div>

</template>

<script>

import FormToggle from './../mixins/FormToggle.vue';
import FormAcl from './../mixins/FormAcl.vue';


export default {
  name: 'ItemComponent',
  components: {
    FormToggle, FormAcl
  },
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false,
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

    },

    handlerToggleChange(event, item, elm) {
      item[elm] = event.value;
      this.handlerSaveForm();
    },

    handlerChangeAcl(newVal) {


      this.$bus.$emit('item--acl', {
        acl: newVal,
        id: this.item.id
      });
    }

  }


};
</script>

<style>

</style>