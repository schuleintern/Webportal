<template>
  <div class="">
    <ul>
      <li v-bind:key="index" v-for="(obj, index) in  item.umfragen"  class="flex-row">
        <div class="flex-5 flex margin-r-l">
          <label>{{ index+1 }}. Frage</label>
          <input type="text" v-model="obj.title" @change="handlerChange">
        </div>
        <div class="flex-1 flex">
          <label>Typ</label>
          <select v-model="obj.typ" @change="handlerChange">
            <option value="boolean">Ja/Nein</option>
            <option value="text">Text</option>
            <option value="number">Zahl</option>
          </select>
        </div>
      </li>
      <li class="padding-0 margin-t-m">
        <button class="si-btn si-btn-green " @click="handlerNewItem()"><i class="fa fa-plus"></i> Frage Hinzufügen</button>
      </li>
    </ul>
  </div>
</template>

<script>

export default {
  components: {

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
    this.item = this.form;
    if (!this.item.umfragen || this.item.umfragen.length < 1) {
      this.handlerNewItem();
    }
  },
  methods: {

    handlerChange() {
      //this.$emit("finish", {'data': this.item})
    },
    handlerNewItem() {

      if (!this.item.umfragen) {
        this.item.umfragen = [];
      }
      this.item.umfragen.push({
        'title': '',
        'typ': 'text'
      });

    },

  }
};
</script>