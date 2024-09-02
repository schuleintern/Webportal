<template>

  <div class="">
    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
      </div>
      <div class="">
        <a class="si-btn si-btn-light" :href="'index.php?page=ext_umfragen&view=list&task=xls&lid='+form.id"><i class="fa fa-download"></i> Xls Download</a>
      </div> 
    </div>



    <div class="">

      <h3>{{ form.title }}</h3>

      <table class="si-table si-table-style-firstLeft">
        <thead>
          <tr>
            <td width="10%"></td>
            <td v-bind:key="index" v-for="(item, index) in  form.childs">
              <h4>{{ index+1 }}. {{ item.title }}</h4>
              <span v-if="item.typ == 'text'" class="text-small">Text</span>
              <span v-if="item.typ == 'number'" class="text-small">Zahl</span>
              <span v-if="item.typ == 'boolean'" class="text-small">Ja/Nein</span>
            </td>
          </tr>
        </thead>
        <tbody>
          <tr v-bind:key="u" v-for="(user, u) in  form.userlist">
            <td>
              <User :data="user" ></User>
            </td>
            <td v-bind:key="index" v-for="(item, index) in  form.childs">
              <AnswerBox :data="getAnswer(user.id, item.id, item.list_id)" :typ="item.typ"></AnswerBox>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</template>

<script>

import User from '../mixins/User.vue'
import AnswerBox from '../mixins/AnswerBox.vue'

export default {
  name: 'AnswerComponent',
  components: {
    User, AnswerBox
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
    item: [],
    answers: Array
  },
  created: function () {
    this.form = this.item;
  },
  methods: {

    getAnswer(userID, itemID, listID) {

      if (this.answers && this.answers[userID]) {
        if (this.answers[userID].data) {
          const ret = this.answers[userID].data.find((item) => {
            if (item.item_id == itemID && item.list_id == listID) {
              return true;
            }
            return false;
          })
          if (ret) {
            return ret.content;
          }
          return false;
        }
      }
      return false;

    },
    
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