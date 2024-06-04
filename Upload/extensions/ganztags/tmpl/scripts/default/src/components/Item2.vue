<template>

  <div class="">

    {{item}}

    <div class="flex-row">
      <div class="flex-1">
        <button class="si-btn si-btn-light" v-on:click="handlerBack"><i class="fa fa-angle-left"></i> Zurück</button>
      </div>
      <div v-show="item.id" class="flex flex-end">
        <button v-show="deleteItem == false" v-on:click="handlerDelete(item)" class="si-btn si-btn-light"><i class="far fa-trash-alt"></i> Löschen</button>
        <button v-show="deleteItem" v-on:click="handlerDeleteSure(item)" class="si-btn si-btn-red"><i class="far fa-trash-alt"></i>Endgültig Löschen!</button>
      </div>
    </div>




  </div>

</template>


<script>

export default {
  components: {
  },
  name: 'Item',
  props: {
    item: Object
  },
  data(){
    return {
      deleteItem: false
    }
  },
  created: function () {
  },
  mounted() {
  },
  methods: {
    handlerBack: function () {
      this.deleteItem = false;
      //this.pagesOpen = false;
      EventBus.$emit('tab--open', {
        tabOpen: 'list'
      });
    },
    handlerDelete: function (item) {
      if (!item.id) {
        return false;
      }
      this.deleteItem = item;
      //this.pagesOpen = false;

    },
    handlerDeleteSure: function () {
      if (!this.item.id) {
        return false;
      }
      this.deleteItem = false;
      //this.pagesOpen = false;
      EventBus.$emit('item-form--delete', {
        item: this.item
      });
    }

  }
}
</script>
