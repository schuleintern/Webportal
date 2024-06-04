<template>

  <div>
    <div class="flex-row">
      <div class="flex-1">
        <a href="#back" class="si-btn si-btn-light" v-on:click="handlerBack"><i class="fa fa-angle-left"></i> Zurück</a>
      </div>
    </div>

    <div class="si-form">

      <ul class="">
        <li :class="required">
          <label>Titel</label>
          <input v-model="form.title" />
        </li>
        <li >
          <label>Info</label>
          <textarea v-model="form.info" ></textarea>
        </li>
        <li>
          <label>End Date</label>
          <Datepicker required :preview-format="format" :format="format" v-model="form.endDate"
                      :enableTimePicker="false" locale="de" cancel-text="Abbrechen"
                      select-text="Ok" :monthChangeOnScroll="false"></Datepicker>

        </li>
        <li class="">
          <label>Benutzer</label>
          <UserSelect @submit="handlerUserSelectMembers" :preselected="form.members"></UserSelect>
          <div class="padding-t-s">
            <span v-bind:key="i" v-for="(o, i) in  form.members" class="margin-b-s margin-r-s blockInline">
              <User v-bind:data="o"></User>
            </span>
          </div>
        </li>
        <li>
          <button @click="submitForm" class="si-btn"><i class="fa fa-save"></i> Speichern</button>
        </li>
      </ul>
    </div>
  </div>
</template>


<script>
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'


import User from "../mixins/User.vue";
import UserSelect from '../mixins/UserSelect.vue'
import {onMounted, ref} from "vue";

export default {
  components: {
    Datepicker, User, UserSelect
  },
  name: 'Form',
  props: {
    item: Object
  },
  setup() {
    const date = ref();

    // For demo purposes assign range from the current date
    onMounted(() => {
      date.value = [new Date()];
    })

    const format = (val) => {
      return `${val.getDate()}.${val.getMonth() + 1}.${val.getFullYear()}`
    }

    return {
      date,
      format
    }
  },
  data(){
    return {

      form: {},

      error: false,
      required: '',
      back: 'list'
    }
  },
  watch: {
    item(newItem) {
      if (newItem) {
        this.form = newItem;
      }
      if (!this.form.members) {
        this.form.members = [];
      }
    }
  },
  created: function () {
  },
  mounted() {
    // access our input using template refs, then focus
  },
  methods: {

    handlerBack: function () {
      this.$bus.$emit('page--open', {
        back: true
      });
    },
    handlerUserSelectMembers: function (userlist) {
      this.form.members = userlist;
      //this.item.members = [...this.item.members, ...userlist];
    },

    submitForm: function () {

      //this.form.date = this.$date(this.form.date).format('YYYY-MM-DD')

      if (!this.form.title) {
        console.log('missing');
        this.required = 'required';
        return false;
      }

      let arr = [];
      if (this.form.members && typeof this.form.members == 'object' && this.form.members.length > 0) {
        this.form.members.forEach((o) => {
          arr.push(o.id);
        });

      }
      this.form.members = arr;

      var that = this;
      this.$bus.$emit('form--submit', {
        item: that.form,
        callback: function () {
          this.$bus.$emit('page--open', {
            back: true
          });
        }
      });
      return false;

    }

  }
}
</script>
