<template>

  <div>
    <div class="flex-row">
      <div class="flex-1">
        <a href="#list" class="si-btn si-btn-light" v-on:click="handlerBack"><i class="fa fa-angle-left"></i> Zurück</a>
      </div>
    </div>

    <div class="si-form">

      <ul class="">
        <li :class="required">
          <label>Titel</label>
          <input v-model="item.title" />
        </li>
        <li :class="required" class="">
          <label>Benutzer</label>
          <UserSelect @submit="handlerUserSelectMembers" :preselected="item.members"></UserSelect>
          <div class="padding-t-s">
            <span v-bind:key="index" v-for="(item, index) in  item.members" class="margin-b-s margin-r-s blockInline">
              <User v-bind:data="item"></User>
            </span>
          </div>
        </li>
        <li>
          <label>Liste Teilen mit</label>

          <UserSelect @submit="handlerUserSelectOwners" :preselected="item.owners"></UserSelect>
          <div class="padding-t-s">
            <span v-bind:key="index" v-for="(item, index) in  item.owners" class="margin-b-s margin-r-s blockInline">
              <User v-bind:data="item"></User>
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
import User from "../mixins/User.vue";
import UserSelect from '../mixins/UserSelect.vue'

export default {
  components: {
    User, UserSelect
  },
  name: 'Form',
  props: {
    item: Object
  },
  data(){
    return {

      error: false,
      required: '',
      back: 'list'
    }
  },
  created: function () {
  },
  mounted() {
    // access our input using template refs, then focus
  },
  methods: {

    handlerBack: function () {

      if (this.item.id) {
        EventBus.$emit('tab--open', {
          tabOpen: 'item'
        });
      } else {
        EventBus.$emit('tab--open', {
          tabOpen: 'list'
        });
      }

    },
    handlerUserSelectMembers: function (userlist) {
      this.item.members = userlist;
      //this.item.members = [...this.item.members, ...userlist];
    },
    handlerUserSelectOwners: function (userlist) {
      this.item.owners = userlist;
      //this.item.members = [...this.item.members, ...userlist];
    },

    submitForm: function () {
      var that = this;
      //this.form.date = this.$date(this.form.date).format('YYYY-MM-DD')

      if (!this.item.title) {
        console.log('missing');
        this.required = 'required';
        return false;
      }

      let members = [];
      if (that.item.members.length > 0) {
        that.item.members.forEach((o) => {
          members.push(o.id);
        });
      } else {
        this.required = 'required';
        return false;
      }
      let owners = [];
      if (that.item.owners.length > 0) {
        that.item.owners.forEach((o) => {
          owners.push(o.id);
        });
      }

      EventBus.$emit('form--submit', {
        item: that.item,
        members: JSON.stringify(members),
        owners: JSON.stringify(owners)
      });

    }

  }
}
</script>
