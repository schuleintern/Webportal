<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">


        <button class="si-btn" v-on:click="handlerAddNew">Add</button>

        <ul>
          <li v-bind:key="index" v-for="(item, index) in formfields">
            {{item}}
            <form v-on:change="handlerChange(item)">
              <select v-model="item.field_id">
                <option v-bind:key="index" v-for="(item, index) in fieldtyp" :value="item.id">{{item.title}}</option>
              </select>
            </form>
          </li>
        </ul>



      </div>
    </div>

  </div>
</template>

<script>


export default {

  components: {

  },
  data() {
    return {
      open: false,
      item: false
    };
  },
  props: {
    formfields: Array | Object,
    fieldtyp: Array | Object
  },
  created: function () {

    var that = this;
    EventBus.$on('modal-item--open', data => {
      that.item = data.item;
      that.formfields = false;
      that.open = true;

      EventBus.$emit('item--getFormfields', {
        id: that.item.id
      });


    });
    EventBus.$on('modal-item--close', data => {
      that.open = false;
    });

  },
  methods: {

    handlerChange: function (item) {

      var that = this;
      EventBus.$emit('item--setFormfields', {
        form_id: that.item.id,
        form: item
      });
    },
    handlerAddNew: function () {
      var that = this;
      EventBus.$emit('item--setFormfields', {
        form_id: that.item.id,
        form: {}
      });
    },
    handlerClose: function () {
      EventBus.$emit('modal-item--close');
    }

  }


};
</script>

<style>

</style>