<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box">
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content ">

        <button v-if="delButton == false" class="si-btn" v-on:click="handlerDelDayGroup(item)">
          <i class="fa fa-trash"></i> Aufsicht entfernen
        </button>
        <button v-if="delButton == true" class="si-btn si-btn-red" v-on:click="handlerDelDayGroupSecond(item)">
          <i class="fa fa-trash"></i> Aufsicht entfernen
        </button>

        <div class="flex-row si-form">
          <div class="flex-2">
            <ul>
              <li>
                <label>Titel</label>
                <div class="padding-l-l">{{ item.title }}</div>
              </li>
              <li>
                <label>Raum</label>
                <div class="padding-l-l">{{ item.room }}</div>
              </li>
              <li>
                <label>Info</label>
                <div class="padding-l-l">{{ item.info }}</div>
              </li>
              <li>
                <label>Aufsicht</label>
                <div class="padding-l-l">{{ item.leader.name }}</div>
              </li>
            </ul>
          </div>
          <div class="flex-1">
            <ul>
              <li>
                <label>Erstellt</label>
                <div class="padding-l-l">{{ item.createdTime }}</div>
              </li>
              <li>
                <label>Von</label>
                <div class="padding-l-l">{{ item.createdBy }}</div>
              </li>
            </ul>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script>


export default {

  components: {},
  data() {
    return {
      open: false,
      item: false,
      delButton: false
    };
  },
  props: {},
  computed: {},
  created: function () {
    var that = this;
    EventBus.$on('modal-info--open', data => {

      if (data.item) {
        that.item = data.item;
      }
      that.open = true;
    });
    EventBus.$on('modal-info--close', data => {
      that.open = false;
      that.item = {};
    });
  },
  methods: {
    handlerDelDayGroup: function (data) {
      this.delButton = true;
    },
    handlerDelDayGroupSecond: function (data) {

      EventBus.$emit('date--delete', {
        item: data
      });
      this.delButton = false;
      EventBus.$emit('modal-info--close');
    },
    handlerClose: function () {
      EventBus.$emit('modal-info--close');
    }
  }


};
</script>

<style>

</style>