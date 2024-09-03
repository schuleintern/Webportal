<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="flex-1">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i>
          Zurück
        </button>
        <button class="si-btn" v-if="showSubmit" @click="handlerSaveForm()"><i class="fa fa fa-save"></i> Speichern</button>
      </div>
      <div class="flex-1 flex-row flex-end">

      </div>
    </div>

{{form}}

    <div class="flex-row">
      <div class="si-details flex-3 margin-r-l">
        <ul class="">
          <li>
            <label>Title</label>
            {{form.title}}
          </li>
          <li >
            <label>Type</label>
            {{form.type}}
          </li>
          <li>
            <label>Name</label>
            {{form.userName}}
          </li>
          <li v-if="form.user">
            <label>E-Mail</label>
            {{form.user.email}}
          </li>
        </ul>
      </div>
      <div class="si-form flex-2 margin-r-l">
        <ul class="">
          <li>
            <label>Anschreibbar</label>
            <FormRules :input="form.isPublic"
                @change="triggerToggleValue($event, 'isPublic')"></FormRules>
          </li>
        </ul>
      </div>
    </div>


  </div>
</template>

<script>

import FormRules from '../mixins/FormRules.vue'


export default {
  name: 'ItemComponent',
  components: {
    FormRules
  },
  data() {
    return {
      form: {},
      required: '',
      showSubmit: false
    };
  },
  setup() {

  },
  props: {
    acl: Array,
    item: []
  },
  created: function () {
    this.form = this.item;
  },
  methods: {

    triggerToggleValue(data, item) {
      this.form[item] = data.value;
      this.showSubmit = true;
    },

    handlerSaveForm() {

      if (!this.form.title) {
        this.required = 'required';
        return false;
      }

      var that = this;
      this.$bus.$emit('item--submit', {
        item: this.form,
        callback: function (data) {
          that.item.id = data.id;
          that.required = '';
        }
      });
      return false;
    },
    handlerBack: function () {
      this.$bus.$emit('page--open', {
        'page': 'list'
      });
    },
    handlerDelete(item) {
      this.$bus.$emit('item--delete', {
        item: item
      });
    }

  }


};
</script>

<style></style>