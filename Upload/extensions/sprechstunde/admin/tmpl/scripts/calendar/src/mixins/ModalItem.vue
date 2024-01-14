<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">

        <table class="si-table">
          <tbody>
          <tr>
            <td><label>Raum</label></td>
            <td>{{unit.room}}</td>
          </tr>
          <tr>
            <td><label>Betreff</label></td>
            <td>{{unit.subject}}</td>
          </tr>
          <tr>
            <td><label>Lehrer</label></td>
            <td>{{unit.teacher}}</td>
          </tr>
          <tr>
            <td><label>Klasse</label></td>
            <td>{{unit.grade}}</td>
          </tr>
          <tr class="text-small">
            <td><label>Erstellt</label></td>
            <td>{{unit.createdTime}}</td>
          </tr>
          </tbody>
        </table>

        <button v-if="unit.createdSelf && !cancelSecond " @click="cancelItemFirst" class="si-btn"><i class="fa fa-save"></i> Stornieren</button>
        <button v-if="unit.createdSelf && cancelSecond" @click="cancelItemSecond" class="si-btn si-btn-red"><i class="fa fa-save"></i> Stornieren</button>

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
    unit: Object
  },
  created: function () {

    var that = this;
    EventBus.$on('modal-item--open', data => {
      that.unit = data.unit;
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
      //console.log(this.unit);
      EventBus.$emit('form--cancel', {
        unit: this.unit
      });

    }
  }


};
</script>

<style>

</style>