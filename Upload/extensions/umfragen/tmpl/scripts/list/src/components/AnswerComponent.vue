<template>

  <div class="">
    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
      </div>
      <div class="">
        <div class="si-btn-multiple">
          <div class="si-btn si-btn-off"><i class="fa fa-download"></i> Export:</div>
          <a class="si-btn" :href="'index.php?page=ext_umfragen&view=list&task=xls&lid='+form.id" target="_blank">xls</a>
          <a class="si-btn" :href="'index.php?page=ext_umfragen&view=list&task=xlsx&lid='+form.id" target="_blank">xlsx</a>
          <a class="si-btn" :href="'index.php?page=ext_umfragen&view=list&task=csv&lid='+form.id" target="_blank">csv</a>
          <a class="si-btn" :href="'index.php?page=ext_umfragen&view=list&task=pdf&lid='+form.id" target="_blank">pdf</a>
        </div>
        </div>
    </div>

    <div class="">

      <h3>{{ form.title }}</h3>

      <table class="si-table si-table-style-firstLeft">
        <thead>
          <tr>
            <td width="20%"></td>
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
            <td >
              <span v-if="user.type == 'inbox'">
                {{user.title}}
              </span>
              <User v-else-if="user.user" :data="user.user" ></User>
            </td>
            <td v-bind:key="index" v-for="(item, index) in  form.childs">
              <AnswerBox :data="getAnswer(user.mid, item.id, item.list_id)" :typ="item.typ"></AnswerBox>
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

    getAnswer(parentID, itemID, listID) {

      if (this.answers && this.answers[parentID]) {
        if (this.answers[parentID].data) {
          const ret = this.answers[parentID].data.find((item) => {
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