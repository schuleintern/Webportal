<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">

        <table class="si-table">
          <tbody>
          <tr>
            <td><label>Titel</label></td>
            <td>{{item.title}}</td>
          </tr>
          <tr>
            <td><label>Uhrzeit</label></td>
            <td>{{item.time}}</td>
          </tr>
          <tr>
            <td><label>Dauer</label></td>
            <td>{{item.duration}}</td>
          </tr>

          </tbody>
        </table>

        <span v-if="acl.delete == 1 && item.createdSelf" class="margin-r-m" >
          <button v-if="!cancelSecond " @click="cancelItemFirst" class="si-btn"><i class="fa fa-save"></i> Entfernen</button>
          <button v-if="cancelSecond" @click="cancelItemSecond" class="si-btn si-btn-red"><i class="fa fa-save"></i> Wirklich Entfernen?</button>
        </span>

        <button v-if="acl.write == 1 && item.createdSelf" @click="editItem" class="si-btn"><i class="fa fa-edit"></i> Bearbeiten</button>

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
      cancelSecond: false
    };
  },
  props: {
    item: Object,
    acl: Object
  },
  created: function () {

    var that = this;
    EventBus.$on('modal-item--open', data => {
      that.item = data.slot;
      that.open = true;
      that.cancelSecond = false;
    });
    EventBus.$on('modal-item--close', data => {
      that.open = false;
    });

  },
  methods: {
    handlerClose: function () {
      EventBus.$emit('modal-item--close');
    },
    cancelItemFirst: function () {
      this.cancelSecond = true;
    },
    cancelItemSecond: function () {
      EventBus.$emit('item--cancel', {
        item: this.item
      });

    },
    editItem: function () {

      this.handlerClose();
      EventBus.$emit('modal-form--open', {
        item: this.item
      });

    }
  }


};
</script>

<style>

</style>