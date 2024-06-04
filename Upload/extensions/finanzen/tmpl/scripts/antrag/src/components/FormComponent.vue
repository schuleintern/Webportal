<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="flex-1">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i>
          Zurück</button>
        <button class="si-btn" @click="handlerSaveForm()"><i class="fa fa fa-save"></i> Speichern</button>
      </div>
      <div class="flex-1 flex-row flex-end">

      </div>
    </div>

    <form class="si-form flex-row">

      <ul class="flex-5">
        <li :class="required">
          <label>Title *</label>
          <input type="text" v-model="form.title" required>
        </li>
        <li :class="required">
          <label>Zahlungsempfänger *</label>
          <input type="text" v-model="form.payee" required>
        </li>
        <li :class="required">
          <label>Benutzer*innen *</label>
          <input type="hidden" v-model="form.users" required>
          <UserSelect @submit="handelerUsers" :preselected="form.users" :prefilter="'isPupil'"></UserSelect>
          <div class="padding-t-s">
            <span v-bind:key="index" v-for="(item, index) in  form.users" class="margin-b-s margin-r-s blockInline">
              <User v-bind:data="item"></User>
            </span>
          </div>
        </li>

      </ul>

      <ul class="flex-3">
        <li :class="required">
          <label>Betrag *</label>
          <div >
            <input type="text" v-model="form.amount_1" required class="width-12rem" placeholder="00">
          ,
          <input type="text" v-model="form.amount_2" required class="width-10rem"> EUR</div>
        </li>
        <li class="">
          <label>Fälligkeitsdatum</label>
          <Datepicker required :preview-format="format" :format="format" v-model="form.endDate"
                        :enableTimePicker="false" locale="de" cancel-text="Abbrechen"
                        select-text="Ok" :monthChangeOnScroll="false"></Datepicker>
        </li>
        <!--
        <li class="">
          <label>Quittung</label>
          <input type="text" v-model="form.receipt">
          <input type="file" />
        </li>
        -->
      </ul>

    </form>

  </div>
</template>

<script>

import User from '../mixins/User.vue'
import UserSelect from '../mixins/UserSelect.vue'

export default {
  name: 'ItemComponent',
  components: {
    UserSelect, User
  },
  data() {
    return {
      form: {},
      required: '',
      deleteBtn: false
    };
  },
  setup() {


    const format = (val) => {
      return `${val.getDate()}.${val.getMonth() + 1}.${val.getFullYear()}`
    }

    return {
      format
    }
  },
  props: {
    acl: Array,
    item: []
  },
  created: function () {
    this.form = this.item;

    if (!this.form.amount_2) {
      this.form.amount_2 = "00";
    }
  },
  methods: {

    handelerUsers: function (data) {
      this.form.users = data;
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
          that.required = '';
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

.dp__theme_light {
  --dp-background-color: #ffffff;
  --dp-text-color: #212121;
  --dp-hover-color: #f3f3f3;
  --dp-hover-text-color: #212121;
  --dp-hover-icon-color: #959595;
  --dp-primary-color: #3c8dbc;
  --dp-primary-text-color: #f8f5f5;
  --dp-secondary-color: #c0c4cc;
  --dp-border-color: #ddd;
  --dp-menu-border-color: #ddd;
  --dp-border-color-hover: #aaaeb7;
  --dp-disabled-color: #f6f6f6;
  --dp-scroll-bar-background: #f3f3f3;
  --dp-scroll-bar-color: #959595;
  --dp-success-color: #018d4e;
  --dp-success-color-disabled: #a3d9b1;
  --dp-icon-color: #959595;
  --dp-danger-color: #dd4b39;
}

.dp__action.dp__cancel,
.dp__action.dp__select {
  box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 15px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px;
  border-radius: 3rem;

  display: inline-block;

  padding: 1rem 1.6rem;
  margin-bottom: 0.3rem;
  margin-top: 0.3rem;

  font-size: 11pt;
  font-weight: 300;
  line-height: 100%;
  letter-spacing: 0.75pt;
  text-align: center;


  vertical-align: middle;
  -ms-touch-action: manipulation;
  touch-action: manipulation;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  background-image: none;

  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  background-color: #3c8dbc;
  border: 1px solid #3c8dbc;
  color: #fff;
}

.dp__action.dp__cancel {
  background-color: #b7c7ce;
  border-color: #b7c7ce;
  color: #fff;
  margin-right: 1rem;
}

.dp__action_row {
  flex-direction: column;
}

.dp__selection_preview,
.dp__action_buttons {
  flex: 1;
  width: 100% !important;
}

.dp__menu {
  font-size: inherit;
}

.dp__selection_preview {
  font-size: 1rem !important;
  padding-bottom: 0.3rem;
  display: flex;
  justify-content: space-around;
}

.dp__input_icons {
  width: 1.5rem;
  height: 1.5rem;
  margin-left: 1rem;
  margin-right: 0.3rem;
}

.dp__input {
  padding-left: 5rem !important;
}
</style>