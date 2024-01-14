<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box">
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">

        <User :data="info.user"></User>
        <br>
        <ul class="flex-row noListStyle">
          <li v-if="info.createDate" class="flex-1 padding-m">
            <label class="">Erstellt:</label>
            <div class="padding-b-m"><i class="fa fa-clock"></i> {{ info.createDate }} - {{ info.createUserID.name }}</div>
            <User v-if="info.createByUser" :data="info.createByUser"></User>
            <div v-if="info.createInfo" class="si-hinweis">{{ info.createInfo }}</div>
            <br><button class="si-btn si-btn-light si-btn-small" v-on:click="handlerDelete('create')"><i class="fa fa-trash"></i> Löschen</button>
          </li>
          <li v-if="info.doneDate" class="flex-1 padding-m">
            <label>Beendet:</label>
            <div class="padding-b-m"><i class="fa fa-clock"></i> {{ info.doneDate }} - {{ info.doneUserID.name }}</div>
            <User v-if="info.doneByUser" :data="info.doneByUser"></User>
            <div v-if="info.doneInfo" class="si-hinweis">{{ info.doneInfo }}</div>
            <br><button class="si-btn si-btn-light si-btn-small" v-on:click="handlerDelete('done')"><i class="fa fa-trash"></i> Löschen</button>
          </li>
        </ul>


      </div>
    </div>

  </div>
</template>

<script>

import User from './User.vue'

export default {

  components: {
    User
  },
  data() {
    return {
      open: false,
    };
  },
  props: {
    info: Object
  },
  created: function () {
    var that = this;
    this.$bus.$on('modal-info--open', () => {
      that.open = true;
    });
    this.$bus.$on('modal-info--close', () => {
      that.open = false;
    });
  },
  methods: {
    handlerDelete: function (type) {

      var that = this;
      if ( window.confirm("Wirklich löschen?") ) {

        this.$bus.$emit('item--delete-count', {
          item: this.info,
          type: type,
          callback: function () {
            that.handlerClose();
          }
        });
      }

    },
    handlerClose: function () {
      this.$bus.$emit('modal-info--close');
    }
  }


};
</script>

<style>

</style>