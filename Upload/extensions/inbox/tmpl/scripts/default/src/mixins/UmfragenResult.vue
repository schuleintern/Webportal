<template>
  <div class="">
    <table class="si-table si-table-style-allLeft">
      <thead>
      <tr>
        <td v-bind:key="index" v-for="(obj, index) in  form.childs">
          <h4>{{ index + 1 }}. {{ obj.title }}</h4>
          <span v-if="obj.typ == 'text'" class="text-small">Text</span>
          <span v-if="obj.typ == 'number'" class="text-small">Zahl</span>
          <span v-if="obj.typ == 'boolean'" class="text-small">Ja/Nein</span>
        </td>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td v-bind:key="index" v-for="(obj, index) in  form.childs">
          <AnswerBox :data="getAnswer(obj.id, obj.list_id)" :typ="obj.typ"></AnswerBox>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>

import AnswerBox from '../mixins/AnswerBox.vue'


export default {
  components: {
    AnswerBox
  },
  data() {
    return {
      item: {}
    };
  },
  props: {
    form: Object
  },
  created: function () {
  },
  methods: {

    getAnswer(itemID, listID) {

      if (this.form.answers ) {
        const ret = this.form.answers.find((item) => {
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

  }
};
</script>