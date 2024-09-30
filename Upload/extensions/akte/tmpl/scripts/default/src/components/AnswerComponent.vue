<template>

  <div class="">
    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück</button>
      </div>
      <div class="">
      </div> 
    </div>



    <div class="">

      <h3>{{ form.title }}</h3>

      <table class="si-table si-table-style-allLeft">
        <thead>
          <tr>
            <td v-bind:key="index" v-for="(item, index) in  form.childs">
              <h4>{{ index+1 }}. {{ item.title }}</h4>
              <span v-if="item.typ == 'text'" class="text-small">Text</span>
              <span v-if="item.typ == 'number'" class="text-small">Zahl</span>
              <span v-if="item.typ == 'boolean'" class="text-small">Ja/Nein</span>
            </td>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td v-bind:key="index" v-for="(item, index) in  form.childs">
              <AnswerBox :data="getAnswer(item.id, item.list_id)" :typ="item.typ"></AnswerBox>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</template>

<script>


import AnswerBox from '../mixins/AnswerBox.vue'

export default {
  name: 'AnswerComponent',
  components: {
    AnswerBox
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

    getAnswer(itemID, listID) {

      if (this.item.answers ) {
          const ret = this.item.answers.find((item) => {
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