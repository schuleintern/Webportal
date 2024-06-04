<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">

        <table class="si-table">
          <tbody>
          <tr>
            <td><label>Raum</label></td>
            <td>{{item.room}}</td>
          </tr>
          <tr>
            <td><label>Betreff</label></td>
            <td>{{item.subject}}</td>
          </tr>
          <tr>
            <td><label>Lehrer</label></td>
            <td>{{item.teacher}}</td>
          </tr>
          <tr>
            <td><label>Klasse</label></td>
            <td>{{item.grade}}</td>
          </tr>
          <tr class="text-small">
            <td><label>Erstellt</label></td>
            <td>{{item.createdTime}}</td>
          </tr>
          </tbody>
        </table>

        <button v-if="item.createdSelf && !cancelSecond " @click="cancelItemFirst" class="si-btn"><i class="fa fa-save"></i> Stornieren</button>
        <button v-if="item.createdSelf && cancelSecond" @click="cancelItemSecond" class="si-btn si-btn-red"><i class="fa fa-save"></i> Stornieren</button>

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
    item: Object
  },
  created: function () {

    var that = this;
    EventBus.$on('modal-item--open', data => {
      that.item = data.item;
      that.open = true;
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
      //console.log(this.item);
      EventBus.$emit('form--cancel', {
        item: this.item
      });

    }
  }


};
</script>

<style>

</style>