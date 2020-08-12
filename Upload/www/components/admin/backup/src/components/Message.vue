<template>
  <div class="message flex flex-10">

    <div
      v-if="message.id"
      class="head">
      <div><label>Von</label>{{message.senderConnect}}</div>
      <div><label>Datum</label>{{message.timeFormat}}</div>
      <div>
        <label>Betreff</label>
        <span v-if="message.priority == 'HIGH'" class="text-red">
          <i class="fa fa-arrow-up"></i>
          Wichtig:
        </span>
        <i v-else-if="message.priority == 'LOW'" class="fa fa-arrow-down text-green"></i>
        {{message.subject}}
      <div>
      <div v-if="message.hasAttachment">
        <label>Anhang</label>
        <i class="fa fa-file-o"></i>
        {{message.hasAttachment}}
      </div>

    </div>
    <div
      v-if="message.id"
      class="body"
      v-html="message.text ">
    </div>
  </div>
</template>

<script>
export default {
  name: 'Message',
  props: {
    message: Object
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
